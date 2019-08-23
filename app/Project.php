<?php

namespace App;

use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
//use Venturecraft\Revisionable\RevisionableTrait;
use Log;

class Project extends Model {

    use HasStatuses;

    protected $table = 'projects';
    protected $fillable = [
        'name', 'project_status', 'boat_id', 'project_type', 'project_progress', 'site_id'
    ];

    protected static function boot() {
        parent::boot();

        Project::observe(ProjectObserver::class);
    }

    public function boat() {
        return $this->belongsTo('App\Boat');
    }

    public function site() {
        return $this->belongsTo('App\Site');
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }

    public function history() {
        return $this->morphMany('App\History', 'historyable');
    }

    public function sections() {
        return $this->belongsToMany('App\Section')
                        ->using('App\ProjectSection')
                        ->withPivot([
                            'project_id',
                            'section_id',
                            'created_at',
                            'updated_at'
        ]);
    }

    public function comments() {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function documents() {
        return $this->morphMany('App\Document', 'documentable');
    }

    public function users() {
        return $this->belongsToMany('App\User')
                        ->using('App\ProjectUser')
                        ->withPivot([
                            // 'role',
                            'profession_id',
                            'created_at',
                            'updated_at'
        ]);
    }

    public function generic_documents() {
        return $this->documents()->where('type', Document::GENERIC_DOCUMENT_TYPE);
    }

    public function addDocumentWithType(\App\Document $doc, $type) {
        if ($type) {
            $doc->type = $type;
        } else {
            $doc->type = \App\Document::GENERIC_DOCUMENT_TYPE;
        }
        $this->documents()->save($doc);
    }

    /**
     * Chiude un progetto o tenta di chiuderlo se trova i task tutti chiusi
     * @param type $force
     */
    public function close($force = 0) {
        
        /** controllo se il progetto ha task che si trovano in 
         *  TASKS_STATUS_DRAFT, TASKS_STATUS_IN_PROGRESS,
         *  TASKS_STATUS_REMARKED, TASKS_STATUS_ACCEPTED  
         * * */
        $foundTasks = $this->tasks()
                        ->where('is_open', '=', 1)
                        ->whereIn('task_status',
                          [
                            TASKS_STATUS_DRAFT, 
                            TASKS_STATUS_SUBMITTED,
                            TASKS_STATUS_ACCEPTED,
                            TASKS_STATUS_IN_PROGRESS,
                            TASKS_STATUS_REMARKED  
                           ]);
                    
        
        if ($foundTasks->count() && !$force) {
            // non posso chiudere il progetto ritorno false
            return ['success'=>false, 'tasks'=>$foundTasks->count()];
        }
        
        if ($foundTasks->count() && $force) {
            // chiudo tutti i ticket che trovo e metto il progetto in stato closed
            $n =  $foundTasks->count();
            foreach ($foundTasks->get() as $task) {
                $task->update(['is_open'=>0]);
            }
            $this->_closeProject();
           return ['success'=>true, 'tasks'=>$n];
        }
            
        if ($foundTasks->count() == 0) {
            // chiudo il progetto e ritorno true
            $this->_closeProject();
            return ['success'=>true, 'tasks'=>$foundTasks->count()];
        }
         
    }
    /**
     * Setta lo stato del progetto a PROJECT_STATUS_CLOSED
     */
    private function _closeProject() {
        $this->update(['project_status'=>PROJECT_STATUS_CLOSED]);
        $this->setStatus(PROJECT_STATUS_CLOSED);
    }
    
    
    /**
     * Chiusura progetto:

      se provo a chiudere un progetto con task in stati differenti da "monitored", "completed" e "denied" la API mi devono impedire di farlo. La webapp puo' chiedere "vuoi chiudere tutti i task ancora aperti?" in questo caso:

      i task in uno degli stati:
      - draft
      - sent to storm

      vengono messi a declined e chiusi

      i task in uno degli stati:
      - added by storm
      - accepted
      - in progress
      - remark

      vengono messi a completed e chiusi

      i task in stato
      - completed

      vengono chiusi

      i task in stato
      - declined

      vengono chiusi

      i task in stato
      - monitored

      vengono lasciati tali (aperti)
     */
}
