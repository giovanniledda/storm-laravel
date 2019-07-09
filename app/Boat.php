<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{

    protected $table = 'boats';

    protected $fillable = [
        'name'
    ];
    /**
     * Get all of the models that own items.
     */

    public function sections()
    {
        return $this->hasMany('App\Section');
    }

    public function projects()
    {
        return $this->hasMany('App\Project');
    }

}
