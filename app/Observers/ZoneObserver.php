<?php

namespace App\Observers;

use App\Zone;

class ZoneObserver
{

    /**
     * @param Zone $zone
     */
    private function allignProjectIdChangesOnChildren(Zone &$zone)
    {
        // Check this only for parent zones
        if (!$zone->parent_zone_id && $zone->isDirty('project_id')) {
            if ($zone->children_zones()->count()) {
                // if project_id is changed for father, then change it for children too
                Zone::where('parent_zone_id', $zone->id)->update([
                    'project_id' => $zone->project_id
                ]);
            }
        }
    }

    /**
     * Handle the zone "created" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function created(Zone $zone)
    {
        $this->allignProjectIdChangesOnChildren($zone);
    }

    /**
     * Handle the zone "updated" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function updated(Zone $zone)
    {
        $this->allignProjectIdChangesOnChildren($zone);
    }

    /**
     * Listen to the Zone updating event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function updating(Zone $zone)
    {
        //
    }

    /**
     * Handle the zone "deleted" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function deleted(Zone $zone)
    {
        //
    }

    /**
     * Handle the zone "restored" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function restored(Zone $zone)
    {
        //
    }

    /**
     * Handle the zone "force deleted" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function forceDeleted(Zone $zone)
    {
        //
    }
}
