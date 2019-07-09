<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'sites';

    protected $fillable = [
        'name'
    ];

    public function projects()
    {
        return $this->hasMany('App\Project');
    }
}
