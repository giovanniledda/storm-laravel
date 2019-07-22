<?php

namespace App\Observers;

use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Task;
use Notification;
use StormUtils;

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
