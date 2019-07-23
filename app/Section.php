<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{

    protected $table = 'sections';

    protected $fillable = [
      'name', 'section_type', 'position', 'code', 'boat_id'
    ];
      
    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function subsections()
    {
        return $this->hasMany('App\Subsection');
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
