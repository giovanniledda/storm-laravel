#!/bin/bash

composer dump-autoload;

# ricostruisco il database
php artisan migrate:fresh;

php artisan config:clear;

# installo passport
php artisan passport:install;

# forzo credenziali passport
mysql -u root -proot storm -e "update oauth_clients set secret='DlsVZZWMTXqWV2c89SfTll5EMWCMatLR5vjeGRlg' where id = 1"
mysql -u root -proot storm -e "update oauth_clients set secret='HiIrGzZSrUggBh6ye56v4g8gGr1i2oO9wiAOkbgk' where id = 2"

# seeder ruoli e permessi
php artisan db:seed --class="RolesAndPermissionsSeeder"

# seeder tabella countries
php artisan db:seed --class="CountriesSeeder"

# crea qualche barca con progetti e task ma in misura minore rispetto agli altri seeder
php artisan db:seed --class="LightSeeder"

