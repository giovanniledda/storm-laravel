<?php

namespace Net7\Documents;

use Illuminate\Support\ServiceProvider;

class DocumentsServiceProvider extends ServiceProvider
{
    /**
     *  Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');


        $this->publishes([
            __DIR__ . '/baseJsonApiClasses/Documents' => config('net7documents.json_api_namespace')
        ]);

        // $this->publishes([
        //     __DIR__ . '/config' => config_path(),
        //     __DIR__ . '/JsonApi/V1/Documents' => config('net7documents.json_api_namespace')
        // ]);
    }

    /**
    * Register the application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->make('Net7\Documents\DocumentsController');
      $this->mergeConfigFrom(__DIR__ . '/config/net7documents.php', 'net7documents');
      $this->mergeConfigFrom(__DIR__ . '/config/medialibrary.php', 'medialibrary');
      $this->mergeConfigFrom(__DIR__ . '/config/json-api-v1.php', 'json-api-v1');
    }
}
