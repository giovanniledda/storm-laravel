<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfessionController;
use App\Http\Controllers\ProjectUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TaskInterventTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => ['logoutBlocked']], function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/user-photo/{id}', [UserController::class, 'getProfilePhoto'])->name('user-photo');

    Route::group(['middleware' => ['auth', 'isAdmin']], function () {

        // Users
        Route::resource('users', UserController::class);

        // Roles and Permissions
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);

        // Sites, Professions, etc.
        Route::resource('sites', SiteController::class);
        Route::resource('professions', ProfessionController::class);
        Route::resource('task_intervent_types', TaskInterventTypeController::class);
        Route::resource('project_user', ProjectUserController::class);

        /** Extra resource routes **/

        // Users
        Route::get('/users/{id}/confirm-destroy', [UserController::class, 'confirmDestroy'])->name('users.confirm.destroy');
        // User Phones
        Route::get('/users/{id}/phones', [UserController::class, 'phonesIndex'])->name('users.phones.index');
        Route::get('/users/{id}/phones/create', [UserController::class, 'phonesCreate'])->name('users.phones.create');
        Route::post('/users/{id}/phones/store', [UserController::class, 'phonesStore'])->name('users.phones.store');
        Route::get('/users/{user_id}/phones/{phone_id}/confirm-destroy', [UserController::class, 'phonesConfirmDestroy'])->name('users.phones.confirm.destroy');
        Route::delete('/users/{user_id}/phones/{phone_id}/destroy', [UserController::class, 'phonesDestroy'])->name('users.phones.destroy');
        // User addresses
        Route::get('/users/{id}/addresses', [UserController::class, 'addressesIndex'])->name('users.addresses.index');
        Route::get('/users/{id}/addresses/create', [UserController::class, 'addressesCreate'])->name('users.addresses.create');
        Route::post('/users/{id}/addresses/store', [UserController::class, 'addressesStore'])->name('users.addresses.store');
        Route::get('/users/{user_id}/addresses/{address_id}/edit', [UserController::class, 'addressesEdit'])->name('users.addresses.edit');
        Route::put('/users/{user_id}/addresses/{address_id}/update', [UserController::class, 'addressesUpdate'])->name('users.addresses.update');
        Route::get('/users/{user_id}/addresses/{address_id}/confirm-destroy', [UserController::class, 'addressesConfirmDestroy'])->name('users.addresses.confirm.destroy');
        Route::delete('/users/{user_id}/addresses/{address_id}/destroy', [UserController::class, 'addressesDestroy'])->name('users.addresses.destroy');

        // Roles and Permissions
        Route::get('/roles/{id}/confirm-destroy', [RoleController::class, 'confirmDestroy'])->name('roles.confirm.destroy');
        Route::get('/permissions/{id}/confirm-destroy', [PermissionController::class, 'confirmDestroy'])->name('permissions.confirm.destroy');

        // Sites
        Route::get('/sites/{id}/confirm-destroy', [SiteController::class, 'confirmDestroy'])->name('sites.confirm.destroy');
        // Site addresses
        Route::get('/sites/{id}/addresses', [SiteController::class, 'addressesIndex'])->name('sites.addresses.index');
        Route::get('/sites/{id}/addresses/create', [SiteController::class, 'addressesCreate'])->name('sites.addresses.create');
        Route::post('/sites/{id}/addresses/store', [SiteController::class, 'addressesStore'])->name('sites.addresses.store');
        Route::get('/sites/{site_id}/addresses/{address_id}/edit', [SiteController::class, 'addressesEdit'])->name('sites.addresses.edit');
        Route::put('/sites/{site_id}/addresses/{address_id}/update', [SiteController::class, 'addressesUpdate'])->name('sites.addresses.update');
        Route::get('/sites/{site_id}/addresses/{address_id}/confirm-destroy', [SiteController::class, 'addressesConfirmDestroy'])->name('sites.addresses.confirm.destroy');
        Route::delete('/sites/{site_id}/addresses/{address_id}/destroy', [SiteController::class, 'addressesDestroy'])->name('sites.addresses.destroy');

        // Professions, Task Types, etc.
        Route::get('/professions/{id}/confirm-destroy', [ProfessionController::class, 'confirmDestroy'])->name('professions.confirm.destroy');
        Route::get('/task_intervent_types/{id}/confirm-destroy', [TaskInterventTypeController::class, 'confirmDestroy'])->name('task_intervent_types.confirm.destroy');
        Route::get('/project_user/{id}/confirm-destroy', [ProjectUserController::class, 'confirmDestroy'])->name('project_user.confirm.destroy');

        // Text Description Suggestions
        Route::get('/suggestions/{suggestion}/confirm-destroy', [SuggestionController::class, 'confirmDestroy'])->name('suggestions.confirm.destroy');
        Route::get('/suggestions/search-context', [SuggestionController::class, 'searchContext'])->name('suggestions.search.context');
        Route::resource('suggestions', SuggestionController::class);
    });
});
