<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Zone;
use function array_key_exists;
use function in_array;
use function md5;
use function throw_if;

class ZonesPersister
{
    /**
     * @param Project $project
     * @param array $zones_data
     * @throws \Throwable
     */
    public function persistZones(Project $project, array $zones_data)
    {
        if (! empty($zones_data)) {
            $parent_to_be_created = [];
            $children_to_be_created = [];  // key (code+desc del padre) => value (array dei figli)
            $new_children_for_existing_parent = [];  // key (id del padre) => value (array dei figli)
            $to_be_updated = [];

            $parent_used_code_descr = [];
            foreach ($zones_data as $parent_zone) {
                $children_for_this_parent = [];
                $code = $parent_zone['attributes']['code'];
                $description = $parent_zone['attributes']['description'];
                $data = [
                    'code' => $code,
                    'description' => $description,
                ];
                $parent_key = md5($code.$description);

                // verifico che non ci sia omonimia tra gli altri nodi parent per lo stesso progetto
                $excluded_ids = isset($parent_zone['id']) ? [$parent_zone['id']] : [];

                throw_if(in_array($parent_key, $parent_used_code_descr) ||
                    $project->countParentZonesByData($data, $excluded_ids),
                    'Exception',
                    "Impossible to create parent Zone [$code, $description]: code+description already taken!");

                $parent_used_code_descr[] = $parent_key;

                $children = $parent_zone['attributes']['children_zones'];
                unset($parent_zone['attributes']['children_zones']);
                if (isset($parent_zone['id'])) {
                    $to_be_updated[$parent_zone['id']] = $parent_zone['attributes'];
                } else {
                    $parent_to_be_created[] = $parent_zone['attributes'];
                }
                $children_used_code_descr = [];
                foreach ($children as $child) {
                    $c_code = isset($child['code']) ? $child['code'] : null;
                    $c_description = isset($child['description']) ? $child['description'] : null;
                    $parent_zone_id = isset($child['parent_zone_id']) ? $child['parent_zone_id'] : null;
                    $md5 = md5($c_code.$c_description);
                    // verifico che non ci sia omonimia tra gli altri nodi children per lo stesso nodo parent
                    $excluded_ids = isset($child['id']) ? [$child['id']] : [];

                    throw_if(in_array($md5, $children_used_code_descr) ||
                        $project->countChildrenZonesByData($parent_zone_id, $child, $excluded_ids),
                        'Exception',
                        "Impossible to create child Zone [$c_code, $c_description]: code+description already taken for parent Zone [$code, $description]!");

                    $children_used_code_descr[] = $md5;
                    if (isset($child['id'])) {
                        $to_be_updated[$child['id']] = $child;
                    } else {
                        $children_for_this_parent[] = $child;
                    }
                }
                if (isset($parent_zone['id'])) {
                    $new_children_for_existing_parent[$parent_zone['id']] = $children_for_this_parent;
                } else {
                    $children_to_be_created[$parent_key] = $children_for_this_parent;
                }
            }

            foreach ($to_be_updated as $id => $zone_data) {
                $zone = Zone::findOrFail($id);
                if (isset($zone_data['id'])) {
                    unset($zone_data['id']);
                }
                $zone->update($zone_data);
            }

            foreach ($parent_to_be_created as $zone_data) {
                $p_zone = Zone::create($zone_data);
                $code = $p_zone->code;
                $description = $p_zone->description;
                $parent_key = md5($code.$description);
                if (array_key_exists($parent_key, $children_to_be_created)) {
                    foreach ($children_to_be_created[$parent_key] as $child_data) {
                        $child_data['parent_zone_id'] = $p_zone->id;
                        Zone::create($child_data);
                    }
                }
            }

            if (! empty($new_children_for_existing_parent)) {
                foreach ($new_children_for_existing_parent as $parent_id => $new_children_data) {
                    foreach ($new_children_data as $child_data) {
                        $c_code = isset($child_data['code']) ? $child_data['code'] : null;
                        $c_description = isset($child_data['description']) ? $child_data['description'] : null;

                        throw_if($project->countChildrenZonesByData($parent_id, $child_data),
                            'Exception',
                            "Impossible to create child Zone [$c_code, $c_description]: code+description already taken for parent Zone [ID: $parent_id]!");

                        $child_data['parent_zone_id'] = $parent_id;
                        Zone::create($child_data);
                    }
                }
            }
        }
    }
}
