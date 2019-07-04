<?php

namespace App\Storm;

use App\Task;
use NorseBlue\Parentity\Traits\IsMtiChildModel;

class StormTask
{
    use IsMtiChildModel;

    protected $table = 'storm_tasks';

    protected $parentModel = Task::class;

    protected $parentEntity = 'entity';

    protected $fillable = [
        'operation_type',
    ];

    public function site()
    {
        return $this->morphOne('App\Storm\StormSite', 'siteable');
    }

    // public function boat(){
    //     return $this->morphedByMany('App\Storm\StormBoat', 'storm_projects');
    // }
}
