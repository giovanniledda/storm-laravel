<?php

namespace App\Storm;

use App\Task;
use Illuminate\Database\Eloquent\Model;
use NorseBlue\Parentity\Traits\IsMtiChildModel;

class StormTask extends Model
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

    public function saySomething()
    {
        return 'Something';
    }

    // public function boat(){
    //     return $this->morphedByMany('App\Storm\StormBoat', 'storm_projects');
    // }
}
