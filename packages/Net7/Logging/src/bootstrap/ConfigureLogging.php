<?php

namespace Net7\Logging\bootstrap;

use Net7\Logging\monolog\handler\EloquentHandler;
use Net7\Logging\monolog\processor\RequestProcessor;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;

class ConfigureLogging extends BaseConfigureLogging {
    public function bootstrap(Application $app) {
        $log = $this->registerLogger($app)->getMonolog(); 
        $log->pushHandler(new EloquentHandler());
        $log->pushProcessor(new RequestProcessor());
    }
}