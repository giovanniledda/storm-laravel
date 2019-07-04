<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $table = 'items';

    protected $fillable = [
        'name'
    ];
    /**
     * Get all of the models that own items.
     */
    public function itemable()
    {
        return $this->morphTo();
    }
    
}
