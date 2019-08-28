

Questo package fornisce la gestione dei file collegandoli ad altre entita'

INSTALL
dopo aver scaricato il package, aggiungere nel file config/app.php


    'providers' => [
        // ...
          Net7\Documents\DocumentsServiceProvider::class

    ]


e nel file composer.json

    "repositories": [
            {
                "type": "path",
                "url": "packages/Net7/Documents/src",
                "options": {
                    "symlink": true
                }
            }
    ]


    "require": [

        // ...
        "net7/documents": "dev-develop"

    ]



    "autoload": {
        "psr-4": {
            // ...
            "Net7\\Documents\\": "packages/Net7/Documents/src/"



Aprire un terminale e digitare i seguenti comandi :

    $ cd CARTELLA_PROGETTO
    $ composer update
    $ php artisan migrate


Pubblicare gli assets:

    $ php artisan vendor:publish



Il package è pronto per essere usato.Il package fornisce delle rotte json-api dichiarate nel file src/routes/routes.php, se necessario si possono sovrascrivere nel file routes/api.php o simili della propria app

Aggiungerle al proprio file di configurazione di json-api (es: config/json-api-v1.php)


    'resources' => [
        'documents' => \Net7\Documents\Document::class,


Se serve cambiare il namespace di jsonapi, modificare la config del package impostando il valore nel file net7documents.php


    'json_api_namespace' => 'App\JsonApi\V1'

USAGE

I model che devono avere documenti collegati devono estendere il model DocumentableModel, es:

    use Net7\Documents\DocumentableModel;

    class Boat extends DocumentableModel
    {
        //...
    }

Se necessario si possono sovrascrivere i metodi forniti dal DocumentableModel, in particolare il metodo

    public function getMediaPath($document){
        return DIRECTORY_SEPARATOR . 'documents'. DIRECTORY_SEPARATOR .  $this->id  . DIRECTORY_SEPARATOR ;

    }

Che definisce la struttura di directory in cui i file andranno a essere salvati.




Il package fornisce delle rotte json-api dichiarate nel file src/routes/routes.php, se necessario si possono sovrascrivere nel file routes/api.php o simili della propria app

