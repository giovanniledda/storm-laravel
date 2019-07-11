<?php

namespace Tests;


use Faker\Factory;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;

abstract class TestApiCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $faker;

    protected $headers = [
        'Content-type' => 'application/vnd.api+json',
        'Accept' => 'application/vnd.api+json',
    ];


    public function setUp(): void {
        parent::setUp();
        $this->faker = Factory::create();

        // To test Oauth Grants
        \Artisan::call('passport:install',['-vvv' => true]);
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}

            public function report(\Exception $e)
            {
                // no-op
            }

            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }

    public function logResponce(\Illuminate\Foundation\Testing\TestResponse $response) {
        echo "\nStatusCode : ".$response->getStatusCode();
        echo "\nResponce : ".$response->getContent();
        echo "\n";
    }

}