<?php

namespace App\Observers;

use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Task;
use Notification;
use StormUtils;
use const TASKS_STATUS_DRAFT;

class TaskObserver
{
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
