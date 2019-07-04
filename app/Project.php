<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;

class Project extends Model
{
    use HasStatuses;

    protected $table = 'projects';

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


    public function tasks()
    {
        return $this->hasMany('App\Task');
    }


    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }
}
