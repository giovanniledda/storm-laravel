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
//    Route::post('login', 'Api\AuthController@login')->name('api.auth.login');  // per ora, nella mobile app non servono
//    Route::post('signup', 'Api\AuthController@signup')->name('api.auth.signup');  // per ora, nella mobile app non servono
    Route::post('reset-password-request', 'Api\AuthController@resetPasswordRequest')->name('api.auth.password.reset');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'Api\AuthController@logout')->name('api.auth.logout');
        Route::get('user', 'Api\AuthController@user')->name('api.auth.user');
    });
});

