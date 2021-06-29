<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$autoIncrement = StormUtils::autoIncrement();

$factory->define(User::class, function (Faker $faker) use ($autoIncrement) {
    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => USER_FAKE_PASSWORD, // password
        'remember_token' => Str::random(10),
    ];
});
