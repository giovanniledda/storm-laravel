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

    // Route::post('api/v1/tasks/{task}/document',  'DocumentController@createRelatedToTask')->name('api:v1:tasks.createDocument');

Route::group(['middleware' => ['auth:api', 'logoutBlocked']], function () {

    JsonApi::register('v1', ['namespace'=>'Api'])->routes(function ($api) {
 
        $api->resource('sites');
        $api->resource('boat-users')->only('create'); // usato solo per associazione boat - user
        $api->resource('project-users')->only('create', 'update'); //->only('create')   ->only('create'); // usato solo per associazione project  - user
        $api->resource('project-sections')->only('create'); //->only('create'); // usato solo per associazione project  - user
        $api->resource('tasks');

        $api->resource('tasks')->only('statuses')->controller('TaskController') //uses the App\Http\Controllers\Api\TaskController
        ->routes(function ($tasks){
                $tasks->get('/statuses', 'statuses')->name('statuses');
            });

        $api->resource('tasks')->only('history')->controller('TaskController') //uses the App\Http\Controllers\Api\TaskController
        ->routes(function ($task){
                $task->get('{record}/history', 'history')->name('history');
            });

        $api->resource('users');
        $api->resource('sections');

        $api->resource('task-intervent-types');

        $api->resource('boats')->relationships(function ($relations) {
            $relations->hasMany('sections'); // punta al methodo dell'adapter /app/jsonApi/boats/Adapter non al modello
            $relations->hasMany('users');
            
        });
       

        $api->resource('boats')->only('owner')->controller('BoatController') //uses the App\Http\Controllers\Api\ProjectsController
        ->routes(function ($boats){
                $boats->post('{record}/owner', 'owner')->name('owner');
        });
        
        $api->resource('projects')->only('statuses', 'close')->controller('ProjectController') //uses the App\Http\Controllers\Api\ProjectsController
        ->routes(function ($projects){
                $projects->get('/statuses', 'statuses')->name('statuses');
                $projects->post('/{record}/close', 'close')->name('close');
            });
        $api->resource('projects')->only('history')->controller('ProjectController') //uses the App\Http\Controllers\Api\ProjectsController
        ->routes(function ($projects){
                $projects->get('{record}/history', 'history')->name('history');
            });
        $api->resource('projects')->relationships(function ($relations) {
            $relations->hasOne('boat'); // punta al methodo dell'adapter /app/jsonApi/Projects/Adapter non al modello
            $relations->hasMany('tasks');
            $relations->hasMany('users');
            $relations->hasMany('sections');
        });

        $api->resource('updates');
        $api->resource('comments');

    });
 });

 /*

JsonApi::register('v1', ['namespace' => 'Api'], function (Api $api) {
    $api->resource('comments', [
        'middleware' => 'json-api.auth:default',
        'has-one' => ['post', 'created-by'],
    ]);
    $api->resource('posts', [
        'middleware' => 'json-api.auth:default',
        'controller' => true,
        'has-one' => 'author',
        'has-many' => ['comments', 'tags']
    ]);
    $api->resource('sites');
});

 */

// Route::group(['prefix' => 'documents'], function() {
//     Route::get('/{document}', 'DocumentController@show') -> name('documents.show');
//     Route::post('/', 'DocumentController@store')->name('documents.store');
// });
