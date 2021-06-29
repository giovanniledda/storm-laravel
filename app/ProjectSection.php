<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectSection extends Pivot
{
    protected $table = 'project_section';

    public $incrementing = true;

    protected $fillable = [
        'section_id',
        'project_id',
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function section()
    {
        return $this->belongsTo('App\Section');
    }
}
