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

    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function site()
    {
        return $this->belongsTo('App\Site');
    }

    public function tasks()
    {
        return $this->hasMany('App\Task');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }

    public function users()
    {
        return $this->belongsToMany('App\Users')
            ->using('App\ProjectUser')
            ->withPivot([
                'role',
                'created_by',
                'updated_by'
            ]);
    }
}
