<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use NorseBlue\Parentity\Traits\IsMtiParentModel;
use Spatie\ModelStatus\HasStatuses;
use Venturecraft\Revisionable\RevisionableTrait;

class Task extends Model
{
    use RevisionableTrait, HasStatuses, IsMtiParentModel;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'estimated_hours',
        'worked_hours',
        'for_admins',
    ];




    /** @optional */
    protected $ownAttributes = [
        'id',
        'title',
        'description',
        'estimated_hours',
        'worked_hours',
        'for_admins',
        'project_id',
        'author_id',
        'project',  // <-- IMPORTANTE: altrimenti chiamate come $task->project->id falliscono per via di IsMtiParentModel
        'entity_type',
        'entity_id',
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function item_part()
    {
        return $this->belongsTo('App\ItemPart');
    }
}
