<?php

namespace App\Jobs;

use App\Notifications\TaskNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Net7\Logging\models\Logs as Log;
use Notification;

class NotifyTaskUpdates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

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
        $this->task_notification = $task_notification;  // Uso la classe base TaskNotifications, ma alla fine passerò un TaskCreated o un TaskUpdated
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
        if (! empty($users)) {
            Notification::send($users, $this->task_notification);
        }
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        Log::error(__(QUEUE_JOB_TASK_UPDATES_FAILED, ['exc_msg' => $exception->getMessage()]));
    }
}
