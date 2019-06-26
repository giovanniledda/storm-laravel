<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Task extends Model
{
    use RevisionableTrait;

    public function project()
    {
        return $this->morphOne('App\Project', 'projectable');
    }
}
