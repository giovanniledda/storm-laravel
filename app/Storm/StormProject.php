<?php

namespace App\Storm;

use App\Project;

class StormProject extends Project
{
    protected $table = 'storm_projects';

//    public function site()
//    {
//        return $this->morphOne('App\Storm\StormSite', 'siteable');
//    }

    // public function boat(){
    //     return $this->morphedByMany('App\Storm\StormBoat', 'storm_projects');
    // }
}
