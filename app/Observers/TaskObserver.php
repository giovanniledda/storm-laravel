<?php

namespace App\Observers;

use App\Models\History;
use App\Jobs\NotifyTaskUpdates;
use App\Jobs\UpdateTaskMap;
use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Models\Project;
use App\Models\Task;
use function is_object;
use const TASKS_STATUS_DRAFT;

class TaskObserver
{
    /**
     * Handle the project "updating" event.
     *
     * @param \App\Models\Task $task
     * @return void
     */
    public function updating(Task $task)
    {
        //   $task->updateMap();

        $original = $task->getOriginal();

        if (isset($original['is_open']) && $original['is_open'] != $task->is_open && $task->is_open == 0) {
            // metto nella history del progetto
            Project::find($task->project_id)
                ->history()
                ->create(
                    ['event_date' => date('Y-m-d H:i:s', time()),
                        'event_body' => 'Task number #'.$task->number.' marked to closed', ]);
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
         */
        if (isset($original['task_status']) && $original['task_status'] != $task->task_status) {
            $auth_user = \Auth::user();
            if (isset($auth_user->id)) {
                $u_id = $auth_user->id;
                $u_fullname = $auth_user->name.' '.$auth_user->surname;
            } elseif ($task->author_id) {
                $u_id = $task->author_id;
                $u_fullname = $task->author->name.' '.$task->author->surname;
            } else {
                $u_id = $u_fullname = null;
            }

            Task::find($task->id)->history()->create([
                'event_date' => date('Y-m-d H:i:s', time()),
                'event_body' => json_encode([
                    'user_id' => $u_id,
                    'user_name' => $u_fullname,
                    'original_task_status' => $original['task_status'],
                    'task_status' => $task->task_status,
                    'comment_id' => null,
                    'comment_body' => null,
                ]),
            ]);
        }
    }

    /**
     * Handle the task "created" event.
     *
     * @param \App\Models\Task $task
     * @return void
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function created(Task $task)
    {
        /**
         * @todo quando inserisci un task da storm lo stato deve essere accepted
         */
        $auth_user = \Auth::user();
        if (isset($auth_user->id)) {
            $u_id = $auth_user->id;
            $u_fullname = $auth_user->name.' '.$auth_user->surname;
        } elseif ($task->author_id) {
            $u_id = $task->author_id;
            $u_fullname = $task->author->name.' '.$task->author->surname;
        } else {
            $u_id = $u_fullname = null;
        }

        // se l'utente non è loggato oppure c'è ma non è storm, metto DRAFT
        if ((isset($auth_user->id) && ! $auth_user->is_storm) || ! \Auth::check()) {
            $task->setStatus(TASKS_STATUS_DRAFT);

            Task::find($task->id)->history()->create([
                'event_date' => date('Y-m-d H:i:s', time()),
                'event_body' => json_encode([
                    'user_id' => $u_id,
                    'user_name' => $u_fullname,
                    'original_task_status' => null,
                    'task_status' => TASKS_STATUS_DRAFT,
                    'comment_id' => null,
                    'comment_body' => null,
                ]),
            ]);
        }

        if ((isset($auth_user->id) && $auth_user->is_storm)) {
            Task::find($task->id)->history()->create([
                'event_date' => date('Y-m-d H:i:s', time()),
                'event_body' => json_encode([
                    'user_id' => $u_id,
                    'user_name' => $u_fullname,
                    'original_task_status' => null,
                    'task_status' => $task->task_status,
                    'comment_id' => null,
                    'comment_body' => null,
                ]),
            ]);
        }

        /** setto la variabile added_by_storm **/
        $task_author = $task->author;
        if (is_object($auth_user)) {
            // se sei in boat_user
            if ($auth_user->can(PERMISSION_BOAT_MANAGER)) {
                $task->update(['added_by_storm' => 0, 'author_id' => $auth_user->id]);
            }
            if ($auth_user->can(PERMISSION_ADMIN) || $auth_user->can(PERMISSION_WORKER) || $auth_user->can(PERMISSION_BACKEND_MANAGER)) {
                $task->update(['added_by_storm' => 1, 'author_id' => $auth_user->id]);
            }
            $task_author = $auth_user;
        }
        UpdateTaskMap::dispatch($task);

        // Setto l'id interno progressivo calcolato su base "per boat"
        $task->updateInternalProgressiveNumber();

        // mette in coda il job
//        NotifyTaskUpdates::dispatch(new TaskCreated($task))->onConnection('redis')->onQueue(QUEUE_TASK_CREATED);   // default queue
        NotifyTaskUpdates::dispatch(new TaskCreated($task, $task_author));   // default queue
    }

    /**
     * Handle the task "updated" event.
     *
     * @param \App\Models\Task $task
     * @return void
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
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
        UpdateTaskMap::dispatch($task);
        NotifyTaskUpdates::dispatch(new TaskUpdated($task));  // default queue
    }

    /**
     * Handle the task "deleted" event.
     *
     * @param \App\Models\Task $task
     * @return void
     */
    public function deleted(Task $task)
    {
        //
    }

    /**
     * Handle the task "restored" event.
     *
     * @param \App\Models\Task $task
     * @return void
     */
    public function restored(Task $task)
    {
        //
    }

    /**
     * Handle the task "force deleted" event.
     *
     * @param \App\Models\Task $task
     * @return void
     */
    public function forceDeleted(Task $task)
    {
        //
    }
}
