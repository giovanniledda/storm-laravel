<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public function project()
    {
        return $this->morphOne('App\Project', 'projectable');
    }
}
