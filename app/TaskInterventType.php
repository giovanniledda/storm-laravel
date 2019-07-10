<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskInterventType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function tasks()
    {
        return $this->hasMany('App\Task');
    }

}
