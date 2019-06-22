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
    Route::resource('users', 'UserController');
    Route::resource('roles', 'RoleController');
    Route::resource('permissions', 'PermissionController');

    // extra resource routes
    Route::get('/users/delete-confirm/{id}', 'UserController@confirmDestroy')->name('users.delete.confirm');
    Route::get('/roles/delete-confirm/{id}', 'RoleController@confirmDestroy')->name('roles.delete.confirm');
    Route::get('/permissions/delete-confirm/{id}', 'PermissionController@confirmDestroy')->name('permissions.delete.confirm');
});