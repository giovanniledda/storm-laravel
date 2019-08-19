<?php

namespace App\Providers;

use CloudCreativity\LaravelJsonApi\LaravelJsonApi;
use Countries;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;


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

        // https://laravel-json-api.readthedocs.io/en/latest/basics/api/
        LaravelJsonApi::defaultApi('v1');

        /**
         * see https://codeburst.io/upload-and-manage-files-with-laravel-and-vue-915378c8b2a4
         * 
         * If you use MariaDB there is can be a problem to work with DBMS via artisan command. 
         * In this case all that you need is using this 
         */

        // TODO: make this work:
        // Schema::defaultStringLength(191);


        /** Blade Components Aliasing [https://laravel.com/docs/5.8/blade#components-and-slots] **/

        /**
         * Generates a select with all the countries as options
         *
         * Usage: @countries(['selected_country' => $country]) @endcountries
         */
        Blade::component('components.countries', 'countries');

        /**
         * Generates a select with all the phone types as options
         *
         * Usage: @phonetypes(['selected_type' => $phone->phone_type]) @endphonetypes
         */
        Blade::component('components.phonetypes', 'phonetypes');

        /**
         * Generates a select with all the professions as options
         *
         * Usage: @stormprofessions(['selected_profession_id' => $selected_profession_id]) @endstormprofessions
         */
        Blade::component('components.stormprofessions', 'stormprofessions');

        /**
         * Generates a select with all the projects as options
         *
         * Usage: @projects(['selected_project_id' => selected_project_id]) @endprojects
         */
        Blade::component('components.projects', 'projects');


        /** Blade extensions [https://laravel.com/docs/5.8/blade#extending-blade] **/

        /**
         * Generates a datetime string
         *
         * Usage: @datetime(time())
         */
        Blade::directive('datetime', function ($expression) {
            return "<?php echo date('m/d/Y H:i', $expression); ?>";
        });

        /**
         * Converts a bool to string: 1 -> 'Yes', 0 -> 'No'
         *
         * Usage: @booltostr($expression)
         */
        Blade::directive('booltostr', function ($expression) {
            $yes = __('Yes');
            $no = __('No');
            return "<?php echo {$expression} == 1  ? '$yes' : '$no'; ?>";
        });


    }
}
