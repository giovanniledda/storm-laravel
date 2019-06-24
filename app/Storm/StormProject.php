<?php

namespace App\Storm;

use Illminate\Database\Eloquent\Model;
use App\Project;

class StormProject extends Project
{
    protected $table = 'storm_projects';


    public function site()
    {
        return $this->morphOne('App\Storm\StormSite', 'siteable');
    }
}
