<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';

    protected $fillable = [
        'name'
    ];

    /**
     * Get all of the models that own projects.
     */
    public function projectable()
    {
        return $this->morphTo();
    }


    public function item()
    {
        return $this->morphOne('App\Item', 'itemable');
    }


    public function site()
    {
        return $this->morphOne('App\Site', 'siteable');
    }


    public function status()
    {
        return $this->morphOne('App\Status', 'statusable');
    }


    public function tasks()
    {
        return $this->hasMany('App\Task');
    }
}
