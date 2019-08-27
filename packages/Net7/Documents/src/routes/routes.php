<?php


Route::group(['middleware' => ['auth:api', 'logoutBlocked']], function () {

    JsonApi::register('v1', ['namespace'=>'Api'])->routes(function ($api) {

        $api->resource('documents')->only('create', 'show', 'show_with_size')->controller('\Net7\Documents\DocumentsController') //uses the App\Http\Controllers\Api\DocumentController
        ->routes(function ($docs){
                $docs->get('{record}/show/{size}', 'show')->name('show_with_size');
                $docs->get('{record}/show', 'show')->name('show');

                // if (config('net7documents.use_collection'))  // aggiungere la variabile al file di conf
                // per attivarla aggiungere 'index' nell'only poco sopra
                // $docs->get('/documents/{entity_type}/{entity_id}', 'index')->name('index');
            });

    });
});

