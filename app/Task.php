<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use Venturecraft\Revisionable\RevisionableTrait;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use RevisionableTrait, HasStatuses;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'estimated_hours',
        'worked_hours',
        'for_admins',
    ];

    public function intervent_type()
    {
        return $this->belongsTo('App\TaskInterventType');
    }

        /**
     * @return BelongsToMany
     */
    public function project()
    {
        return $this->belongsToMany(Project::class);
    }
    /*
    public function project()
    {
        return $this->belongsTo('App\Project');
    }*/

    public function subsection()
    {
        return $this->belongsTo('App\Subsection');
    }

    public function author()
    {
        return $this->belongsTo('App\User');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }

}
