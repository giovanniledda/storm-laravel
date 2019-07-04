<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPart extends Model
{

    protected $table = 'item_parts';

    protected $fillable = [
        'name'
    ];
      
    public function item()
    {
        return $this->morphOne('App\Item', 'itemable');
    }

}
