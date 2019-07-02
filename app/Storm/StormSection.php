<?php

namespace App\Storm;

use App\ItemPart;

class StormSection extends ItemPart
{
    private $table = 'storm_sections';
    
    public function item()
    {
        return $this->morphOne('App\Storm\StormBoat', 'itemable');
    }

}
