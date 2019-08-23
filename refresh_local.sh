#!/bin/bash

composer dump-autoload;

#ricostruisco il database
php artisan migrate:fresh;

php artisan config:clear;

#installo passport
php artisan passport:install;

#seeder ruoli e permessi
php artisan db:seed --class="RolesAndPermissionsSeeder"

#seeder tabella countries
php artisan db:seed --class="CountriesSeeder"

echo -e "/ncrea un sito n barche n professioni e relative sezioni/n crea n utenti con vari ruoli e li associa alle barche/n crea n progetti e li associa alle barche e agli utenti" 
php artisan db:seed --class="DatabaseSeeder"




