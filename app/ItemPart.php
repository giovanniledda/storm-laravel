<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPart extends Model
{
    

    protected $fillable = [
        'name'
    ];
      
    public function item()
    {
        return $this->morphOne('App\Item', 'itemable');
    }

}
