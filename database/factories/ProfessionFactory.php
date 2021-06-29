<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Profession;
use Faker\Generator as Faker;

$autoIncrement = StormUtils::autoIncrement();

$factory->define(Profession::class, function (Faker $faker) use ($autoIncrement) {
    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->jobTitle,
    ];
});
