<?php

use CloudCreativity\LaravelJsonApi\Facades\JsonApi;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * spostare la rotta di soppra sotto un middlewere protetto e verificare successivamente.
 */

Route::group(['middleware' => ['auth:api', 'logoutBlocked']], function () {

    JsonApi::register('v1', ['namespace' => 'Api'])->routes(function ($api) {

        $api->resource('sites');
        $api->resource('boat-users')->only('create'); // usato solo per associazione boat - user
        $api->resource('project-users')->only('create', 'update'); //->only('create')   ->only('create'); // usato solo per associazione project  - user
        $api->resource('project-sections')->only('create'); //->only('create'); // usato solo per associazione project  - user
        $api->resource('tasks');

        $api->resource('tasks')->only('statuses')->controller('TaskController')//uses the App\Http\Controllers\Api\TaskController
        ->routes(function ($tasks) {
            $tasks->get('/statuses', 'statuses')->name('statuses');
        });

        $api->resource('tasks')->only('history')->controller('TaskController')//uses the App\Http\Controllers\Api\TaskController
        ->routes(function ($task) {
            $task->get('{record}/history', 'history')->name('history');
        });

        $api->resource('users');
        
        $api->resource('users')->only('closed-projects')->controller('UserController') //uses the App\Http\Controllers\Api\UserController
        ->routes(function ($boats) {
            $boats->post('{record}/update-photo', 'updatePhoto')->name('update-photo');
        });

        $api->resource('sections');

        $api->resource('task-intervent-types');

        $api->resource('boats')->relationships(function ($relations) {
            $relations->hasMany('sections'); // punta al methodo dell'adapter /app/jsonApi/boats/Adapter non al modello
            $relations->hasMany('users');

        });


        $api->resource('boats')->only('owner', 'closed-projects')->controller('BoatController')//uses the App\Http\Controllers\Api\ProjectController
        ->routes(function ($boats) {
            $boats->post('{record}/owner', 'owner')->name('owner');
            $boats->get('{record}/closed-projects', 'closedProjects')->name('closed-projects');
        });

        $api->resource('projects')->only(
            'statuses',
            'close',
            'history',
            'change-type')->controller('ProjectController')//uses the App\Http\Controllers\Api\ProjectsController
        ->routes(function ($projects) {
            $projects->get('/statuses', 'statuses')->name('statuses');
            $projects->post('/{record}/close', 'close')->name('close');
            $projects->get('{record}/history', 'history')->name('history');
            $projects->post('/{record}/change-type', 'changeType')->name('change-type');
        });

        $api->resource('projects')->relationships(function ($relations) {
            $relations->hasOne('boat'); // punta al methodo dell'adapter /app/jsonApi/Projects/Adapter non al modello
            $relations->hasMany('tasks');
            $relations->hasMany('users');
            $relations->hasMany('sections');
        });

        $api->resource('updates');
        $api->resource('updates')->only('mark-read')->controller('UpdateController') //uses the App\Http\Controllers\Api\UpdateController
        ->routes(function ($boats) {
            $boats->get('{record}/mark-read', 'markAsRead')->name('mark-read');
        });

        $api->resource('comments');


        $api->resource('documents')->only('show')->controller('DocumentsController') //uses the App\Http\Controllers\Api\DocumentController
        ->routes(function ($docs) {
            $docs->get('{record}/show/{size}', 'show')->name('show_with_size');
            $docs->get('{record}/show', 'show')->name('show');
            $docs->post('create', 'create')->name('create');
        });


    });
});
