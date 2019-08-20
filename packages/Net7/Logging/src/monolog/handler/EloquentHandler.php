<?php
namespace Net7\Logging\monolog\handler;

use \Monolog\Handler\AbstractProcessingHandler;
use Net7\Logging\models\Logs;

class EloquentHandler extends AbstractProcessingHandler {
    protected function write(array $record) {
        Logs::create([
            'env'     => $record['channel'],
            'message' => $record['message'],
            'level'   => $record['level_name'],
            'context' => $record['context'],
            'extra'   => $record['extra']
        ]);
    }
}