<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Boat;
use App\Utils\Utils;
use Faker\Generator as Faker;

$autoIncrement = StormUtils::autoIncrement();

$factory->define(Boat::class, function (Faker $faker) use ($autoIncrement) {
    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->sentence(3),
        'registration_number' => $faker->randomNumber(5),
        'flag' => $faker->country(),
        'manufacture_year' => $faker->year(),
        'length' => $faker->randomFloat(4, 1, 200),
        'draft' => $faker->randomFloat(4, 1, 40),
        'beam' => $faker->randomFloat(4, 1, 3),
        'boat_type' => $faker->randomElement([BOAT_TYPE_MOTOR, BOAT_TYPE_SAIL]),
    ];
});
