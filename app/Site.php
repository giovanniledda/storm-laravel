<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{

    protected $fillable = [
        'name'
    ];

    /**
     * Get all of the models that own sites.
     */
    public function siteable()
    {
        return $this->morphTo();
    }
}
