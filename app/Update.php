<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// NOTA BENE: non usare mai questa classe per scrivere, ma SOLO PER LEGGERE sul DB (per creare/aggiornare le "notifications").
// Creata solo per ottenere una risorsa da usare con le JsonAPI

class Update extends Model
{
    // È la tabella standard che Laravel mette a disposizione per le notifiche
    protected $table = 'notifications';


    public function getMessage() {
        return 'Someone just created a new task on project X!';
    }

    public function getTaskId() {
        return 1;
    }
}
