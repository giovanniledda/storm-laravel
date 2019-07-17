<?php

namespace Tests;

use Faker\Factory;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use App\User;


//abstract class TestApiCase extends BaseTestCase
abstract class TestApiCase extends TestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $faker;
    private $log = true; // pushare con fase

    protected $headers = [
        'Content-type' => 'application/vnd.api+json',
        'Accept' => 'application/vnd.api+json',
    ];


    public function setUp(): void {
        parent::setUp();
        // // To test Oauth Grants
        // \Artisan::call('passport:install',['-vvv' => true]);
        Passport::actingAs(factory(User::class)->create());
    }


    public function logResponce(\Illuminate\Foundation\Testing\TestResponse $response) {
        if ($this->log) {
            echo "\nStatusCode : ".$response->getStatusCode();
            echo "\nResponce : ".$response->getContent();
            echo "\n";
        }
    }


}
