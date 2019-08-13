<?php

namespace App\Observers;

use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Task;
use App\History;
use App\Project;
use function is_object;
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
    public function updating(Task $task)
    {
        $original       = $task->getOriginal();

        if (isset($original['is_open']) &&  $original['is_open']!=$task->is_open && $task->is_open==0) {
             // metto nella history del progetto
             Project::find($task->project_id)
                            ->history()
                            ->create(
                                    ['event_date'=> date("Y-m-d H:i:s", time()),
                                     'event_body'=>'Task number #'.$task->number.' marked to closed']);
         }


      /*
        $revisions      = new Revisions();



        /** parte che impatta sullo storico dei progetti **/

        // è cambiato lo stato del task
     /*   if ($original['task_status']!=$task->task_status && $task->task_status==TASKS_STATUS_CLOSED) {
            /*
            $c = $revisions->join('tasks', 'revisions.revisionable_id', '=',  'tasks.id')
                   ->where('tasks.project_id', '=', $task->project_id)
                   ->where('revisions.key', '=', 'task_status')
                   ->where('revisions.new_value', 'like', TASKS_STATUS_CLOSED)
                   ->where('revisions.created_at', 'like', substr($task->updated_at, 0,10).'%')
                   ->groupBy('tasks.id')->count();
            */

       /*     $c = $revisions->join('tasks', 'statuses.model_id', '=',  'tasks.id')
                   ->where('tasks.project_id', '=', $task->project_id)
                   ->where('statuses.name', '=', TASKS_STATUS_CLOSED)
                    ;


            Log::info($c);

            /*
             *  se il task è stato TASKS_STATUS_CLOSED allora conto tutti i task chiusi
             *  nello stesso giorno e scrivo l'evento
             *
             *  TODO : vedere se si puo usare insert or update con eloquent bisogna anche usare like
             */

            /*
            $projectHistory->updateOrInsert(
                    ['project_id' => $task->project_id, 'event_type' => PROJECT_EVENT_TYPE_MARK_COMPLETED],
                    ['author_id' => $user->id, 'project_id'=>$task->project_id, 'author_id'=>$user->id,'event'=>$c .' '.PROJECT_EVENT_MARK_COMPLETED ]
            );
            */

     /*       $eventExist= $projectHistory
                      ->where('project_id', '=', $task->project_id)
                      ->where('event_type', '=', PROJECT_EVENT_TYPE_MARK_COMPLETED)
                      ->where('created_at', 'like', substr($task->updated_at, 0,10).'%');
            if ($eventExist->count()) {
                //update event
                $eventExist->update(['event'=>$c .' '.PROJECT_EVENT_MARK_COMPLETED]);
            } else {
                // write event
                //  'author_id','project_id','event'

                ProjectHistory::create([
                    'project_id'=>$task->project_id,
                    'event_type'=>PROJECT_EVENT_TYPE_MARK_COMPLETED,
                    'author_id'=>  $user->id,
                    'event'=>$c .' '.PROJECT_EVENT_MARK_COMPLETED
                    ]);
            }
       }  */

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

        /** setto la variabile added_by_storm **/
        $user = \Auth::user(); 
        
        if (is_object($user)) {
            // se sei in boat_user
            if ($user->can(PERMISSION_BOAT_MANAGER)) {
                $task->update(['added_by_storm'=>0, 'author_id'=>$user->id]);
            }
            if ($user->can(PERMISSION_ADMIN) || $user->can(PERMISSION_WORKER) || $user->can(PERMISSION_BACKEND_MANAGER)) {
                $task->update(['added_by_storm'=>1, 'author_id'=>$user->id]);
            } 
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
        if ($task->task_status) {
            $task->setStatus($task->task_status);
        }
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
