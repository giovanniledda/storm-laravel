<?php

namespace Net7\Logging\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\DetectEnvironment::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class, 
         Net7\Logging\bootstrap\ConfigureLogging::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    // ...
}