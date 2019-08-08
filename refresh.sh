#!/bin/bash

#ricostruisco il database
php artisan migrate:fresh

#installo passport
php artisan passport:install

#seeder ruoli e permessi
php artisan db:seed --class="RolesAndPermissionsSeeder"

#seeder ambiente stage
php artisan db:seed --class="StageSeeder"
