<?php

namespace App\Notifications;

use App\Task;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use function is_null;
use function is_object;
use function sprintf;
use StormUtils;
use const TASK_CREATED_MOBILE_APP_TEXT;

class TaskNotifications extends Notification
{
    use Queueable;

    public $task;
    public $actionAuthor;  // who did the action that fires the notification

    /**
     * Create a new notification instance.
     *
     * @param $task
     * @param $author
     * @return void
     */
    public function __construct(Task $task, User $author = null)
    {
        $this->task = $task;
        $this->actionAuthor = $author;
        if (is_null($this->actionAuthor) && Auth::check()) {
            $this->actionAuthor = Auth::user();
        }

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
            'task_author_id' => $this->task->author_id,
            'action_author_id' => $this->actionAuthor ? $this->actionAuthor->id : null,
            'section_id' =>  $this->task->section_id,
            'project_id' => $this->getProjectId(),
            'project_name' => $this->getProjectName(),
            'boat_id'=> (string) $this->getBoatId(), // casto a string perchè nel json mi serve formattarlo tipo boat_id : "2"
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
