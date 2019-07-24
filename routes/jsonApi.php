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

/*
 * AUTHENTICATION ROUTES
 */

// Route::group(['prefix' => 'auth'], function () {
// //    Route::post('login', 'API\AuthController@login')->name('api.auth.login');  // per ora, nella mobile app non servono
// //    Route::post('signup', 'API\AuthController@signup')->name('api.auth.signup');  // per ora, nella mobile app non servono
//     Route::post('reset-password-request', 'API\AuthController@resetPasswordRequest')->name('api.auth.reset_pwd_request');

//     Route::group(['middleware' => 'auth:api'], function () {
//         Route::get('logout', 'API\AuthController@logout')->name('api.auth.logout');
//         Route::get('user', 'API\AuthController@user')->name('api.auth.user');
//     });
// });


// Route::post('/v1/documents', 'DocumentController@create')->name('api:v1:documents.create');


// Route::group(['middleware' => 'auth:api'], function () {

    Route::post('api/v1/tasks/{task}/document',  'DocumentController@createRelatedToTask')->name('api:v1:tasks.createDocument');
    Route::get('api/v1/project-statuses',  'ProjectController@projectStatuses')->name('api:v1:project-statuses');
    Route::get('api/v1/task-statuses',  'TaskController@taskStatuses')->name('api:v1:task-statuses');
// }

Route::group(['middleware' => 'auth:api'], function () {

    JsonApi::register('v1', ['namespace'=>'Api'])->routes(function ($api) {
        // $api->resource('documents')->controller('DocumentController') ; // uses the App\Http\Controllers\Api\DocumentController
        // $api->resource('documents')->except('create');
        $api->resource('documents');
        // $api->resource('documents')->only('create')->middleware('convertFileFromBase64');

        $api->resource('sites'); 
        $api->resource('boat-users');
        $api->resource('tasks');
        $api->resource('sections');
        $api->resource('task-intervent-types');
        
        $api->resource('boats')->relationships(function ($relations) { 
            $relations->hasMany('sections'); // punta al methodo dell'adapter /app/jsonApi/boats/Adapter non al modello
        });
        
        
        $api->resource('projects')->relationships(function ($relations) {
            $relations->hasOne('boat'); // punta al methodo dell'adapter /app/jsonApi/Projects/Adapter non al modello
            $relations->hasMany('tasks');
        });

        $api->resource('updates');

        // $api->resource('documents')->controller('DocumentController')->only('create');
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
