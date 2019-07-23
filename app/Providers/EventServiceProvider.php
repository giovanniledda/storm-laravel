<?php

namespace App\Providers;

use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Project;
use App\Task;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        /*
         * Per ora provo col Subscriber (vedi sotto).
         * L'alternativa è:
        'App\Events\TaskCreated' => [
            'App\Listeners\SendTaskCreatedNotification',
        ]
        */
    ];


    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\TaskSubscriber',
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     * Event Discovery is available for Laravel 5.8.9 or later:
     * When Laravel finds any listener class method that begins with handle, Laravel will register those methods as
     * event listeners for the event that is type-hinted in the method's signature
     *
     * @return bool
     */
    /*
    public function shouldDiscoverEvents()
    {
        return true;
    }
    */

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);
    }
}
