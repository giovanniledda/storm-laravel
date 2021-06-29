<?php

namespace App\JsonApi\V1\Zones;

use App\Models\Task;
use App\Models\Zone;
use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{
    /**
     * @var string
     */
    protected $resourceType = 'zones';

    /**
     * @var array
     */
    protected $relationships = [
        'parent_zone',
        'children_zones',
        'project',
    ];

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            // campi
            'code' => $resource->code,
            'description' => $resource->description,
            'extension' => $resource->extension,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
            // relazioni
            'parent_zone' => $resource->parent_zone,  // parent_zone - zones
            'children_zones' => $resource->children_zones,  // children_zones - zones
            'project' => $resource->project_for_api,  // children_zones - zones
        ];
    }

    /**
     * @param Zone $zone
     * @param bool $isPrimary
     * @param array $includedRelationships
     * @return array
     */
    public function getRelationships($zone, $isPrimary, array $includedRelationships)
    {
        return [
            'parent_zone' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includedRelationships['parent_zone']),
                self::DATA => function () use ($zone) {
                    return $zone->parent_zone;
                },
            ],
            'children_zones' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includedRelationships['children_zones']),
                self::DATA => function () use ($zone) {
                    return $zone->children_zones;
                },
            ],
            'project' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includedRelationships['project']),
                self::DATA => function () use ($zone) {
                    return $zone->project_for_api;
                },
            ],
        ];
    }
}
