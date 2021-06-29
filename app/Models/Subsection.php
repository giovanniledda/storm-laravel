<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsection extends Model
{
    use HasFactory;

    protected $fillable = [
        'storm_id',
        'name',
        'comment',
    ];

    public function section()
    {
        return $this->belongsTo(\App\Models\Section::class);
    }

    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class);
    }
}
