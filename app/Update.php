<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
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

    public function getUpdateId() {
        return isset($this->getAttributes()['id']) ? $this->getAttributes()['id'] : $this->id;
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

    public function getBoatId() {
        $data = $this->getDataArray();
        return isset($data['boat_id']) ? $data['boat_id'] : null;
    }

    public function getProjectName() {
        $data = $this->getDataArray();
        return isset($data['project_name']) ? $data['project_name'] : null;
    }

    public function getProjectId() {
        $data = $this->getDataArray();
        return isset($data['project_id']) ? $data['project_id'] : null;
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function markAsRead()
    {
        $notification = DatabaseNotification::where('id', $this->id)->get();
        if ($notification) {
            $notification->markAsRead();
            return $notification;
        }
        return null;
    }
}
