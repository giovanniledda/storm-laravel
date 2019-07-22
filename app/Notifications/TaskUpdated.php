<?php

namespace App\Notifications;

use App\Task;
use StormUtils;
use const TASK_CREATED_MOBILE_APP_TEXT;
use const TASK_UPDATED_MOBILE_APP_TEXT;

class TaskUpdated extends TaskNotifications
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        parent::__construct($task);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->getProjectId(),
            'title' => $this->task->title,
            'description' => $this->task->description,
            'message' => $this->getMobileAppMessage(),
        ];
    }

    protected function getMobileAppMessage()
    {

        StormUtils::replacePlaceholders(TASK_UPDATED_MOBILE_APP_TEXT, [
            '@someone' => 'Someone',
            '@task_id' => $this->task->id,
            '@project_name' => $this->getProjectName(),
            '@boat_name' => $this->getBoatName(),
        ]);
    }
}