<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleTaskCreation($event) {

    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\TaskCreated',
            'App\Listeners\TaskSubscriber@handleTaskCreation'
        );
    }
}