<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{

    protected $table = 'sections';

    protected $fillable = [
        'name'
    ];
      
    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function tasks()
    {
        return $this->hasManyThrough('App\Task', 'App\Subsection');
    }

    public function map_image()
    {
        return $this->morphOne('App\Document', 'documentable');
    }
}
