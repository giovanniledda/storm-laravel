<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TaskSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleTaskCreation($event)
    {
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        /*
        $events->listen(
            'App\Events\TaskCreated',
            'App\Listeners\TaskSubscriber@handleTaskCreation'
        );
        */
    }
}
