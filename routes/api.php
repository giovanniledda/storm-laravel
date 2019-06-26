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

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'API\AuthController@login')->name('api.auth.login');
    Route::post('signup', 'API\AuthController@signup')->name('api.auth.signup');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'API\AuthController@logout')->name('api.auth.logout');;
        Route::get('user', 'API\AuthController@user')->name('api.auth.user');;
    });
});


Route::post('/v1/documents', 'DocumentController@create')->name('api:v1:documents.create');

// Route::group(['middleware' => 'auth:api'], function () {
        
    JsonApi::register('v1')->routes(function ($api) {
        // $api->resource('documents')->controller('DocumentController') ; // uses the App\Http\Controllers\Api\DocumentController
        $api->resource('documents')->except('create');
        $api->resource('projects');
        // $api->resource('documents')->controller('DocumentController')->only('create');
    });
// });


JsonApi::register('storm')->routes(function ($api) {
    $api->resource('documents')->except('create');
    $api->resource('projects');
});

// Route::group(['prefix' => 'documents'], function() {
//     Route::get('/{document}', 'DocumentController@show') -> name('documents.show');
//     Route::post('/', 'DocumentController@store')->name('documents.store');
// });
