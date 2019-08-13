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
    Route::get('/users/{id}/delete-confirm', 'UserController@confirmDestroy')->name('users.delete.confirm');
    Route::get('/roles/{id}/delete-confirm', 'RoleController@confirmDestroy')->name('roles.delete.confirm');
    Route::get('/permissions/{id}/delete-confirm', 'PermissionController@confirmDestroy')->name('permissions.delete.confirm');

    // Sites, Boats & Co.
    Route::get('/sites/{id}/delete-confirm', 'SiteController@confirmDestroy')->name('sites.delete.confirm');
    Route::get('/sites/{id}/addresses', 'SiteController@addressesIndex')->name('sites.addresses.index');
    Route::get('/sites/{id}/addresses/create', 'SiteController@addressesCreate')->name('sites.addresses.create');
    Route::post('/sites/{id}/addresses/store', 'SiteController@addressesStore')->name('sites.addresses.store');
    Route::get('/sites/{site_id}/addresses/{address_id}/edit', 'SiteController@addressesEdit')->name('sites.addresses.edit');
    Route::post('/sites/{site_id}/addresses/{address_id}/update', 'SiteController@addressesUpdate')->name('sites.addresses.update');
    Route::get('/sites/{site_id}/addresses/{address_id}/delete-confirm', 'SiteController@addressesConfirmDestroy')->name('sites.addresses.delete.confirm');
    Route::delete('/sites/{site_id}/addresses/{address_id}/destroy', 'SiteController@addressesDestroy')->name('sites.addresses.destroy');

    Route::get('/professions/{id}/delete-confirm', 'ProfessionController@confirmDestroy')->name('professions.delete.confirm');
    Route::get('/task_intervent_types/{id}/delete-confirm', 'TaskInterventTypeController@confirmDestroy')->name('task_intervent_types.delete.confirm');
});