<?php

namespace App\Storm;

use App\Project;
use NorseBlue\Parentity\Traits\IsMtiChildModel;

class StormProject
{
    use IsMtiChildModel;

    protected $table = 'storm_projects';

    protected $parentModel = Project::class;

    protected $parentEntity = 'entity';

    protected $fillable = [
        'type',
        'acronym',
    ];

//    public function site()
//    {
//        return $this->morphOne('App\Storm\StormSite', 'siteable');
//    }

    // public function boat(){
    //     return $this->morphedByMany('App\Storm\StormBoat', 'storm_projects');
    // }
}
