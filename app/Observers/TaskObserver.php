<?php

namespace App\Observers;

use App\Jobs\NotifyTaskUpdates;
use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Task;
use App\History;
use App\Project;
use function is_object;
use Notification;
use const QUEUE_TASK_UPDATED;
use StormUtils;
use Net7\Logging\models\Logs as Log;

use const TASKS_STATUS_DRAFT;


class TaskObserver
{


    /**
     * Handle the project "updating" event.
     *
     * @param  \App\Task $task
     * @return void
     */
    public function updating(Task $task)
    {
        $original = $task->getOriginal();

        if (isset($original['is_open']) && $original['is_open'] != $task->is_open && $task->is_open == 0) {
            // metto nella history del progetto
            Project::find($task->project_id)
                ->history()
                ->create(
                    ['event_date' => date("Y-m-d H:i:s", time()),
                        'event_body' => 'Task number #' . $task->number . ' marked to closed']);
           
        }
        
        /**
         * per la history del task occorre scrivere nella history del task 
         * nel campo event_body bisogna scrivere una payload così formata :
         *  {
         *      user_id : 1,
         *      user_name : 'pippo',
         *      comment_id: 2,
         *      comment_body: 'this is a comment' ,
         *      task_status : 'open',
         *      original_task_status : '',
         *      
         *  }
         * 
         */
        if (isset($original['task_status']) && $original['task_status']!=$task->task_status) {
            $user = \Auth::user();
            Task::find($task->id)->history()->create([
                'event_date' => date("Y-m-d H:i:s", time()),
                'event_body' => json_encode([
                    'user_id'=>$user->id,
                    'user_name'=>$user->name.' '.$user->surname,
                    'original_task_status'=>$original['task_status'],
                    'task_status'=>$task->task_status,
                    'comment_id'=>null,
                    'comment_body'=>null,
                ])
            ]);
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
     * @param  \App\Task $task
     * @return void
     */
    public function created(Task $task)
    {
        $task->setStatus(TASKS_STATUS_DRAFT);
        /**
         * @todo quando inserisci un task da storm lo stato deve essere accepted
         */
         
        $user = \Auth::user();
        if (isset($user->id)) { 
            Task::find($task->id)->history()->create([
                'event_date' => date("Y-m-d H:i:s", time()),
                'event_body' => json_encode([
                    'user_id'=>$user->id,
                    'user_name'=>$user->name.' '.$user->surname,
                    'original_task_status'=>null,
                    'task_status'=>TASKS_STATUS_DRAFT,
                    'comment_id'=>null,
                    'comment_body'=>null,
                ])
            ]);
        }
         
        /** setto la variabile added_by_storm **/
        $task_author = null;
        if (is_object($user)) {
            // se sei in boat_user
            if ($user->can(PERMISSION_BOAT_MANAGER)) {
                $task->update(['added_by_storm' => 0, 'author_id' => $user->id]);
            }
            if ($user->can(PERMISSION_ADMIN) || $user->can(PERMISSION_WORKER) || $user->can(PERMISSION_BACKEND_MANAGER)) {
                $task->update(['added_by_storm' => 1, 'author_id' => $user->id]);
            }
            $task_author = $user;
        }

        // mette in coda il job
//        NotifyTaskUpdates::dispatch(new TaskCreated($task))->onConnection('redis')->onQueue(QUEUE_TASK_CREATED);   // default queue
        NotifyTaskUpdates::dispatch(new TaskCreated($task, $task_author));   // default queue

//        Log::info('foo');
    }

    /**
     * Handle the task "updated" event.
     *
     * @param  \App\Task $task
     * @return void
     */
    public function updated(Task $task)
    {
        // devo notificare anche all'aggiornamento perché in fase di creazione il task potrebbe
        // non essere stato associato ad un progetto e quindi non avere utenti
        if ($task->task_status) {
            $task->setStatus($task->task_status);
        }

        // mette in coda il job
//        NotifyTaskUpdates::dispatch(new TaskUpdated($task))->onConnection('redis')->onQueue(QUEUE_TASK_UPDATED);  // default queue
        NotifyTaskUpdates::dispatch(new TaskUpdated($task));  // default queue
    }

    /**
     * Handle the task "deleted" event.
     *
     * @param  \App\Task $task
     * @return void
     */
    public function deleted(Task $task)
    {
        //
    }

    /**
     * Handle the task "restored" event.
     *
     * @param  \App\Task $task
     * @return void
     */
    public function restored(Task $task)
    {
        //
    }

    /**
     * Handle the task "force deleted" event.
     *
     * @param  \App\Task $task
     * @return void
     */
    public function forceDeleted(Task $task)
    {
        //
    }
}
