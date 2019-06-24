<?php

namespace App\Storm;

use Illuminate\Database\Eloquent\Model;
use App\ItemPart;

class StormSection extends ItemPart
{
    private $table = 'storm_sections';
    
    public function item()
    {
        return $this->morphOne('App\Storm\StormBoat', 'itemable');
    }

}
