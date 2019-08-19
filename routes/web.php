<?php

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

    Route::get('/home', 'HomeController@index')->name('home');

    Route::group(['middleware' => ['auth', 'isAdmin']], function () {

        // Users
        Route::resource('users', 'UserController');

        // Roles and Permissions
        Route::resource('roles', 'RoleController');
        Route::resource('permissions', 'PermissionController');

        // Sites, Professions, etc.
        Route::resource('sites', 'SiteController');
        Route::resource('professions', 'ProfessionController');
        Route::resource('task_intervent_types', 'TaskInterventTypeController');
        Route::resource('project_user', 'ProjectUserController');

        /** Extra resource routes **/

        // Users
        Route::get('/users/{id}/confirm-destroy', 'UserController@confirmDestroy')->name('users.confirm.destroy');
        // User Phones
        Route::get('/users/{id}/phones', 'UserController@phonesIndex')->name('users.phones.index');
        Route::get('/users/{id}/phones/create', 'UserController@phonesCreate')->name('users.phones.create');
        Route::post('/users/{id}/phones/store', 'UserController@phonesStore')->name('users.phones.store');
        Route::get('/users/{user_id}/phones/{phone_id}/confirm-destroy', 'UserController@phonesConfirmDestroy')->name('users.phones.confirm.destroy');
        Route::delete('/users/{user_id}/phones/{phone_id}/destroy', 'UserController@phonesDestroy')->name('users.phones.destroy');
        // User addresses
        Route::get('/users/{id}/addresses', 'UserController@addressesIndex')->name('users.addresses.index');
        Route::get('/users/{id}/addresses/create', 'UserController@addressesCreate')->name('users.addresses.create');
        Route::post('/users/{id}/addresses/store', 'UserController@addressesStore')->name('users.addresses.store');
        Route::get('/users/{user_id}/addresses/{address_id}/edit', 'UserController@addressesEdit')->name('users.addresses.edit');
        Route::put('/users/{user_id}/addresses/{address_id}/update', 'UserController@addressesUpdate')->name('users.addresses.update');
        Route::get('/users/{user_id}/addresses/{address_id}/confirm-destroy', 'UserController@addressesConfirmDestroy')->name('users.addresses.confirm.destroy');
        Route::delete('/users/{user_id}/addresses/{address_id}/destroy', 'UserController@addressesDestroy')->name('users.addresses.destroy');


        // Roles and Permissions
        Route::get('/roles/{id}/confirm-destroy', 'RoleController@confirmDestroy')->name('roles.confirm.destroy');
        Route::get('/permissions/{id}/confirm-destroy', 'PermissionController@confirmDestroy')->name('permissions.confirm.destroy');

        // Sites
        Route::get('/sites/{id}/confirm-destroy', 'SiteController@confirmDestroy')->name('sites.confirm.destroy');
        // Site addresses
        Route::get('/sites/{id}/addresses', 'SiteController@addressesIndex')->name('sites.addresses.index');
        Route::get('/sites/{id}/addresses/create', 'SiteController@addressesCreate')->name('sites.addresses.create');
        Route::post('/sites/{id}/addresses/store', 'SiteController@addressesStore')->name('sites.addresses.store');
        Route::get('/sites/{site_id}/addresses/{address_id}/edit', 'SiteController@addressesEdit')->name('sites.addresses.edit');
        Route::put('/sites/{site_id}/addresses/{address_id}/update', 'SiteController@addressesUpdate')->name('sites.addresses.update');
        Route::get('/sites/{site_id}/addresses/{address_id}/confirm-destroy', 'SiteController@addressesConfirmDestroy')->name('sites.addresses.confirm.destroy');
        Route::delete('/sites/{site_id}/addresses/{address_id}/destroy', 'SiteController@addressesDestroy')->name('sites.addresses.destroy');

        // Professions, Task Types, etc.
        Route::get('/professions/{id}/confirm-destroy', 'ProfessionController@confirmDestroy')->name('professions.confirm.destroy');
        Route::get('/task_intervent_types/{id}/confirm-destroy', 'TaskInterventTypeController@confirmDestroy')->name('task_intervent_types.confirm.destroy');
        Route::get('/project_user/{id}/confirm-destroy', 'ProjectUserController@confirmDestroy')->name('project_user.confirm.destroy');
    });
});
