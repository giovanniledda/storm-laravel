<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Task extends Model
{
    protected $table = 'task';

    use RevisionableTrait;

    protected $fillable = [
        'title'
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function status()
    {
        return $this->morphOne('App\Status', 'statusable');
    }
}
