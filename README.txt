README

Installazione di un nuovo progetto "base".
I comandi da lanciare saranno preceduti da un asterisco (*).
Le azioni da intraprendere (creazione file, compilazione istruzioni, etc) saranno precedute da un trattino (-).

Sezione base

- Creare un DB con i dati inseriti nel file .env alle righe "DB_*"



* php artisan key:generate

Il comando crea una entry nel file .env:

APP_KEY=base64:wAw8OnEiCvNtIkLIZ5iW6E1jjhEMr2STsWSFWdrpUbQ=

* php artisan migrate

Il comando crea le tabelle di partenza per tutta la app (comprese quelle necessarie per il funzionamento dei "moduli" di autenticazione e ruoli/permessi, vedere sezioni seguenti).


- Creare un file .env.testing con le seguenti impostazioni per il DB:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=storm_testing
DB_USERNAME=storm
DB_PASSWORD=storm

- Creare il DB di testing di conseguenza, con i dati sopra


Sezione autenticazione (Passport)

* php artisan passport:install

Il comando crea due client per il consumo delle API dell'applicazione. I due client si differenziano per il tipo di meccanismo grant con cui poi negozieranno il token access di Oauth2:
- client ID 1: tipo di grant "Personal access" (è possibile generare di volta in volta un token per l'utente che si vuole registrare o loggare al sito senza necessità delle credenziali del client)
- client ID 2: tipo di grant "Password" (per ottenere il token, il cliente deve passare per una fase di autenticazione prevista dal protocollo (vedere: https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/))

- Le credenziali dei due client vengono mostrate in output dal comando sopra; devono essere salvate nel file .env (e anche nel file .env.testing) in questo modo:


# Personal access client
PASSPORT_PERSONAL_AC_ID=1   (reale)
PASSPORT_PERSONAL_AC_NAME="Storm-Laravel Personal Access Client"   (esempio)
PASSPORT_PERSONAL_AC_SECRET=5uchAQdoCsohVoYx0zHCwmGVx2u9SxmmBZrE2ekL   (esempio)
# Password grant client
PASSPORT_PASSWORD_AC_ID=2   (reale)
PASSPORT_PASSWORD_AC_NAME="Storm-Laravel Password Grant Client"   (esempio)
PASSPORT_PASSWORD_AC_SECRET=kzsScSVY4BvbYLQMwGFkDhXxAz7XNrs2K9XgGCMX     (esempio)


Sezione Ruoli e Permessi

- Nel file .env creare le credenziali per l'Admin, in questo modo:

# Default Admin user
ADMIN_USERNAME=admin@storm-laravel.com     (esempio)
ADMIN_PASSWORD=admin     (esempio)

* composer dump-autoload

* php artisan db:seed --class="RolesAndPermissionsSeeder"

Il comando è interattivo (con prompt sulla CLI) e permette di creare dei ruoli e permessi di base per l'applicazione.
I ruoli di base sono "Admin" e "User", i permessi di base sono salvati nel seguente file: config/constants.php (che quindi può essere modificato a piacimento).

Se il comando viene lanciato più volte (per errore) i ruoli e permessi esistenti non vengono duplicati, ma le assegnazioni dei permessi per i ruoli di User e Admin vengono sovrascritte.

































