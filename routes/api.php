<?php

use App\Http\Controllers\Api;
use CloudCreativity\LaravelJsonApi\Facades\JsonApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
//    Route::post('login', [Api\AuthController::class, 'login'])->name('api.auth.login');  // per ora, nella mobile app non servono
//    Route::post('signup', [Api\AuthController::class, 'signup'])->name('api.auth.signup');  // per ora, nella mobile app non servono
    Route::post('reset-password-request', [Api\AuthController::class, 'resetPasswordRequest'])->name('api.auth.password.reset');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', [Api\AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('user', [Api\AuthController::class, 'user'])->name('api.auth.user');

        // recupero di un documento non pubblico
        Route::get('download_document_web/{documentId}/{size?}', [Api\DocumentsController::class, 'downloadDocumentWeb'])->name('download_document_web');
    });
});
