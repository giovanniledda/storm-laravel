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

# seeder dati vari su tutti (o quasi) i modelli
echo -e "\n crea un sito, n barche, n professioni e relative sezioni, \n crea n utenti con vari ruoli e li associa alle barche, \n crea n progetti e li associa alle barche e agli utenti"
php artisan db:seed --class="DatabaseSeeder"




