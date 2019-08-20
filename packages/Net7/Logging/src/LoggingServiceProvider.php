<?php

namespace Net7\Logging;

use Illuminate\Support\ServiceProvider;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    { 
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
      //   $this->app->make('wisdmLabs\todolist\TodolistController');
    }
}
