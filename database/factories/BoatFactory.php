<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Boat;
use Faker\Generator as Faker;

$factory->define(Boat::class, function (Faker $faker) {

    return [
        'name' => $faker->sentence(3),
        'registration_number' => $faker->randomNumber(5),
        'flag' => $faker->country(),
        'manufacture_year' => $faker->year(),
        'length' => $faker->randomFloat(4, 1, 200),
        'draft' => $faker->randomFloat(4, 1, 40),
        'beam' => $faker->randomFloat(4, 1, 3),
    ];
});
