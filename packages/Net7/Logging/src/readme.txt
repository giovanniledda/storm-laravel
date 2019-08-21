Questo package si occupa di inserire in una tabella Logs le logs per utente nello standard monolog

INSTALL
dopo aver scaricato il package, aprire un terminale e digitare i seguenti comandi :


#cd CARTELLA_PROGETTO
#composer update
#php artisan migrate:fresh

il package è pronto per essere usato.

USAGE

Può essere usata nel codice così come segue :


use Net7\Logging\models\Logs as Log;

class yourClass {
        Log::info("this is a log");
}

sarà creato un record nella tabella logs di tipo INFO, altre opzioni possono essere :
 
   notice($message, $context = null) 
   warning($message, $context = null)
   error($message, $context = null)
   critical($message, $context = null)
   alert($message, $context = null)
   emergency($message, $context = null)
 
context può essere un oggetto un'array o una string o null.


