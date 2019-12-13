<?php

namespace App\Observers;

use App\Zone;

class ZoneObserver
{
    /**
     * Handle the zone "created" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function created(Zone $zone)
    {
        //
    }

    /**
     * Handle the zone "updated" event.
     *
     * @param \App\Zone $zone
     * @return void
     */
    public function updated(Zone $zone)
    {
        // Check this only for parent zones
        if (!$zone->parent_zone && $zone->isDirty('project_id')) {
            if ($zone->children_zones()->count()) {
                Zone::where('parent_zone_id', $zone->id)->update([
                    'project_id' => $zone->project_id
                ]);

//                foreach ($zone->children_zones as $children_zone) {
//                    $children_zone->project()->associate($zone->project);
//                    $children_zone->save();
//                    dd($children_zone);
//                }
            }
        }
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
