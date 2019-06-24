<?php

namespace App\Storm;

use Illuminate\Database\Eloquent\Model;
use App\Item;

class StormBoat extends Item
{
    protected $table = 'storm_boats';
    

    public function project()
    {
        return $this->morphOne('App\Storm\StormProject', 'projectable');
    }
}
