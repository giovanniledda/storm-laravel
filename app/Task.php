<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use Venturecraft\Revisionable\RevisionableTrait;

class Task extends Model
{
    protected $table = 'tasks';

    use RevisionableTrait, HasStatuses;

    protected $fillable = [
        'title'
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }
}
