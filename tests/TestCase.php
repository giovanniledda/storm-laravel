<?php

namespace Tests;

use App\Exceptions\Handler;
use Faker\Factory;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use function in_array;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('it_IT');

        // To test Oauth Grants
        \Artisan::call('passport:install', ['-vvv' => true]);
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct()
            {
            }

            public function report(\Exception $e)
            {
                // no-op
            }

            public function render($request, \Exception $e)
            {
                throw $e;
            }
        });
    }

    protected function checkAllFields($object, $fields, $except = [])
    {
        foreach ($fields as $field => $value) {
            if (! in_array($field, $except)) {
                $this->assertNotNull($object->{$field}, "Controllo su campo [$field]");
            }
        }
    }
}
