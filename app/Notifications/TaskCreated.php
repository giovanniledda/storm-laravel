<?php

namespace App\Notifications;

use App\Task;
use App\User;
use StormUtils;
use const TASK_CREATED_MOBILE_APP_TEXT;

class TaskCreated extends TaskNotifications
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task, User $author = null)
    {
        parent::__construct($task, $author);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $parent_result = parent::toDatabase($notifiable);
        $parent_result['message'] = $this->getMobileAppMessage();
        return $parent_result;
    }

    protected function getMobileAppMessage()
    {

        return StormUtils::replacePlaceholders(TASK_CREATED_MOBILE_APP_TEXT, [
            '@someone' => $this->actionAuthor ? $this->actionAuthor->getFullName() : 'Someone',
            '@task_id' => $this->task->id,
            '@project_name' => $this->getProjectName(),
            '@boat_name' => $this->getBoatName(),
            ]);
    }
}
