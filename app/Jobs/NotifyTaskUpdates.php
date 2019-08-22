<?php

namespace App\Jobs;

use App\Notifications\TaskNotifications;
use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Notification;

class NotifyTaskUpdates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    protected $task;
    protected $task_notification;

    /**
     * Create a new job instance.
     *
     * @param TaskNotifications $task
     * @return void
     */
    public function __construct(TaskNotifications $task_notification)
    {
        $this->task = $task_notification->task;
        $this->task_notification = $task_notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $users = StormUtils::getAllBoatManagers();
        $users = $this->task->getUsersToNotify();
        if (!empty($users)) {
            Notification::send($users, new TaskNotifications($this->task_notification));
        }

//        $users = $this->task->getUsersToNotify();
//        if (!empty($users)) {
//            Notification::send($users, new TaskCreated($this->task));
//        }
    }
}
