<?php

namespace App\Notifications;

use App\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use function is_object;
use function sprintf;
use StormUtils;
use const TASK_CREATED_MOBILE_APP_TEXT;

class TaskNotifications extends Notification
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
//        return ['database', 'mail']; // ci servirà a breve
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
/*
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }
*/

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
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
            'project_name' => $this->getProjectName(),
            'boat_name' => $this->getBoatName(),
            'title' => $this->task->title,
            'description' => $this->task->description,
        ];
    }


    protected function getProjectId()
    {
        return is_object($this->task->project) ? $this->task->project->id : null;
    }

    protected function getProjectName()
    {
        return is_object($this->task->project) ? $this->task->project->name : null;
    }

    protected function getBoatId()
    {
        return is_object($this->task->getProjectBoat()) ? $this->task->getProjectBoat()->id : null;
    }

    protected function getBoatName()
    {
        return is_object($this->task->getProjectBoat()) ? $this->task->getProjectBoat()->name : null;
    }

}
