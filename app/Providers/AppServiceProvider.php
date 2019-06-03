<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        /**
         * see https://codeburst.io/upload-and-manage-files-with-laravel-and-vue-915378c8b2a4
         * 
         * If you use MariaDB there is can be a problem to work with DBMS via artisan command. 
         * In this case all that you need is using this 
         */

        // TODO: make this work:
        // Schema::defaultStringLength(191);
    
        //
    }
}
