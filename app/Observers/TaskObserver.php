<?php

namespace App\Observers;

use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Task; 
use App\ProjectHistory;
use App\Revisions;

use Notification;
use StormUtils;
use Log;


use const TASKS_STATUS_DRAFT;


class TaskObserver
{
      
    
    /**
     * Handle the project "updating" event.
     *
     * @param  \App\Project  $project
     * @return void
     */
    public function saved(Task $task)
    {  
        $original       = $task->getOriginal(); 
        $revisions      = new Revisions();
        $projectHistory = new ProjectHistory();
        
        /** parte che impatta sullo storico dei progetti **/
        
        // è cambiato lo stato del task
        if ($original['task_status']!=$task->task_status && $task->task_status==TASKS_STATUS_CLOSED) {
            
            $c = $revisions->join('tasks', 'revisions.revisionable_id', '=',  'tasks.id') 
                   ->where('tasks.project_id', '=', $task->project_id) 
                   ->where('revisions.key', '=', 'task_status')
                   ->where('revisions.new_value', 'like', TASKS_STATUS_CLOSED)
                   ->where('revisions.created_at', 'like', substr($task->updated_at, 0,10).'%')
                   ->groupBy('tasks.id')->count(); 
            
            Log::info($c);
            
            /*
             *  se il task è stato TASKS_STATUS_CLOSED allora conto tutti i task chiusi 
             *  nello stesso giorno e scrivo l'evento  
             * 
             *  TODO : vedere se si puo usare insert or update con eloquent
             */
             
             $eventExist= $projectHistory
                      ->where('project_id', '=', $task->project_id)
                      ->where('event_type', '=', PROJECT_EVENT_TYPE_MARK_COMPLETED)
                      ->where('created_at', 'like', substr($task->updated_at, 0,10).'%');
            if ($eventExist->count()) {
                //update event
                $eventExist->update(['event'=>$c .' '.PROJECT_EVENT_MARK_COMPLETED]);
            } else {
                // write event
                //  'author_id','project_id','event'
                $user = \Auth::user();
                ProjectHistory::create([
                    'project_id'=>$task->project_id,
                    'event_type'=>PROJECT_EVENT_TYPE_MARK_COMPLETED,
                    'author_id'=>  $user->id,
                    'event'=>$c .' '.PROJECT_EVENT_MARK_COMPLETED
                    ]);
            } 
       }  
        
    }
    
    
    /**
     * Handle the task "created" event.
     *
     * @param  \App\Task  $task
     * @return void
     */
    public function created(Task $task)
    {
        $task->setStatus(TASKS_STATUS_DRAFT);

//        $users = StormUtils::getAllBoatManagers();
        $users = $task->getUsersToNotify();
        if (!empty($users)) {
            Notification::send($users, new TaskCreated($task));
        }
    }

    /**
     * Handle the task "updated" event.
     *
     * @param  \App\Task  $task
     * @return void
     */
    public function updated(Task $task)
    {
        // devo notificare anche all'aggiornamento perché in fase di creazione il task potrebbe
        // non essere stato associato ad un progetto e quindi non avere utenti
        $task->setStatus($task->task_status);
        $users = $task->getUsersToNotify();
        if (!empty($users)) {
            Notification::send($users, new TaskUpdated($task));
        }
    }

    /**
     * Handle the task "deleted" event.
     *
     * @param  \App\Task  $task
     * @return void
     */
    public function deleted(Task $task)
    {
        //
    }

    /**
     * Handle the task "restored" event.
     *
     * @param  \App\Task  $task
     * @return void
     */
    public function restored(Task $task)
    {
        //
    }

    /**
     * Handle the task "force deleted" event.
     *
     * @param  \App\Task  $task
     * @return void
     */
    public function forceDeleted(Task $task)
    {
        //
    }
}
