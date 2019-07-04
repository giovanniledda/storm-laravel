<?php

namespace App\Storm;

use App\Task;

class StormTask extends Task
{
    protected $table = 'storm_tasks';

    public function site()
    {
        return $this->morphOne('App\Storm\StormSite', 'siteable');
    }

    // public function boat(){
    //     return $this->morphedByMany('App\Storm\StormBoat', 'storm_projects');
    // }
}
