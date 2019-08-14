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

Route::get('/home', 'HomeController@index')->name('home');

Route::group( ['middleware' => ['auth', 'isAdmin']], function() {
    // Users
    Route::resource('users', 'UserController');
    Route::resource('roles', 'RoleController');
    Route::resource('permissions', 'PermissionController');

    // Sites, Boats & Co.
    Route::resource('sites', 'SiteController');
    Route::resource('professions', 'ProfessionController');
    Route::resource('task_intervent_types', 'TaskInterventTypeController');

    /** Extra resource routes **/

    // Users
    Route::get('/users/{id}/confirm-destroy', 'UserController@confirmDestroy')->name('users.confirm.destroy');
    Route::get('/roles/{id}/confirm-destroy', 'RoleController@confirmDestroy')->name('roles.confirm.destroy');
    Route::get('/permissions/{id}/confirm-destroy', 'PermissionController@confirmDestroy')->name('permissions.confirm.destroy');

    // Sites, Boats & Co.
    Route::get('/sites/{id}/confirm-destroy', 'SiteController@confirmDestroy')->name('sites.confirm.destroy');
    Route::get('/sites/{id}/addresses', 'SiteController@addressesIndex')->name('sites.addresses.index');
    Route::get('/sites/{id}/addresses/create', 'SiteController@addressesCreate')->name('sites.addresses.create');
    Route::post('/sites/{id}/addresses/store', 'SiteController@addressesStore')->name('sites.addresses.store');
    Route::get('/sites/{site_id}/addresses/{address_id}/edit', 'SiteController@addressesEdit')->name('sites.addresses.edit');
    Route::put('/sites/{site_id}/addresses/{address_id}/update', 'SiteController@addressesUpdate')->name('sites.addresses.update');
    Route::get('/sites/{site_id}/addresses/{address_id}/confirm-destroy', 'SiteController@addressesConfirmDestroy')->name('sites.addresses.confirm.destroy');
    Route::delete('/sites/{site_id}/addresses/{address_id}/destroy', 'SiteController@addressesDestroy')->name('sites.addresses.destroy');

    Route::get('/professions/{id}/confirm-destroy', 'ProfessionController@confirmDestroy')->name('professions.confirm.destroy');
    Route::get('/task_intervent_types/{id}/confirm-destroy', 'TaskInterventTypeController@confirmDestroy')->name('task_intervent_types.confirm.destroy');
});