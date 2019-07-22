<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function is_object;
use Spatie\ModelStatus\HasStatuses;
use Venturecraft\Revisionable\RevisionableTrait;
use App\Events\TaskCreated;

class Task extends Model
{
    use RevisionableTrait, HasStatuses;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'number',
        'estimated_hours',
        'worked_hours',
        'for_admins',
        'status',
        'project_id',
        'section_id',
        'author_id',
        'intervent_type_id'
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TaskCreated::class,
    ];

    public function intervent_type()
    {
        return $this->belongsTo('App\TaskInterventType');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function getProjectBoat()
    {
//        $this->hasOneThrough('App\Boat','App\Project'); // così non funziona perché va a cercare 'projects.task_id' in 'field list' (SQL: select `boats`.*, `projects`.`task_id` as `laravel_through_key` from `boats` inner join `projects` on `projects`.`id` = `boats`.`project_id` where `projects`.`task_id` = 13 limit 1)'
        return $this->project ? $this->project->boat : null;
    }

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
     public function taskIntervents()
    {
        return $this->hasOne('App\TaskInterventType');
//        return $this->hasOneThrough('App\Site', 'App\Project');  // NON funziona perché i progetti sono "many" e il site è "one"
    }
     

    public function getUsersToNotify() {

        $proj = $this->project;
        if (is_object($proj)) {
            $users = $proj->users;
            if (!empty($users)) {
                // aggiungere qua altra logica, se serve (tipo filtri sui ruoli, etc)
                return $users;
            }
        }
        return [];
    }
    
    public static function boot()
    {
        parent::boot(); 
        self::created(function($model){ 
            $model->setStatus(TASKS_STATUSES[0]);
        });
 
    }
}
