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


Route::post('/v1/documents', 'DocumentController@create')->name('api:v1:documents.create');

Route::group(['middleware' => 'auth:api'], function () {

    JsonApi::register('v1', ['namespace'=>'Api'])->routes(function ($api) {
        // $api->resource('documents')->controller('DocumentController') ; // uses the App\Http\Controllers\Api\DocumentController
        $api->resource('documents')->except('create');
        $api->resource('boats');
        $api->resource('tasks');
        $api->resource('projects')->relationships(function ($relations) {
            $relations->hasOne('boat'); // punta al methodo dell'adapter /app/jsonApi/Projects/Adapter non alla risorsa.
            $relations->hasMany('tasks');
        });

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
