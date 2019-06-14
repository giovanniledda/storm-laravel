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
        Route::get('logout', 'API\AuthController@logout');
        Route::get('user', 'API\AuthController@user');
    });
});



JsonApi::register('v1')->routes(function ($api) {
    $api->resource('documents');
    // $api->resource('comments');
});

// Route::group(['prefix' => 'documents'], function() {
//     Route::get('/{document}', 'DocumentController@show') -> name('documents.show');
//     Route::post('/', 'DocumentController@store')->name('documents.store');
// });
