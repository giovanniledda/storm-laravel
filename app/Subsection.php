<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subsection extends Model
{
    protected $fillable = [
        'storm_id',
        'name',
        'comment',
    ];

    public function section()
    {
        return $this->belongsTo(\App\Section::class);
    }

    public function tasks()
    {
        return $this->hasMany(\App\Task::class);
    }
}
