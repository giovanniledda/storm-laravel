<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function json_decode;

// NOTA BENE: non usare mai questa classe per scrivere, ma SOLO PER LEGGERE sul DB (per creare/aggiornare le "notifications").
// Creata solo per ottenere una risorsa da usare con le JsonAPI

class Update extends Model
{
    // È la tabella standard che Laravel mette a disposizione per le notifiche
    protected $table = 'notifications';

    public function getDataArray()
    {
        // per vedere com'è fatto "data" bisogna controllare nella toDatabase della Notification specifica che viene creata
        return json_decode($this->data, true);
    }

    public function getMessage() {
        $data = $this->getDataArray();
        return isset($data['message']) ? $data['message'] : null;
    }

    public function getTaskId() {
        $data = $this->getDataArray();
        return isset($data['task_id']) ? $data['task_id'] : null;
    }

    public function getBoatName() {
        $data = $this->getDataArray();
        return isset($data['boat_name']) ? $data['boat_name'] : null;
    }

    public function getProjectName() {
        $data = $this->getDataArray();
        return isset($data['project_name']) ? $data['project_name'] : null;
    }
}
