<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Storm\StormTask;
use Faker\Generator as Faker;

$factory->define(StormTask::class, function (Faker $faker) {
    return [
        'number' => $faker->randomDigitNotNull(),
        'title' => $faker->sentence(),
        'description' => $faker->text(),
        'estimated_hours' => $faker->randomFloat(1, $min = 0, $max = 100),
        'worked_hours' => $faker->randomFloat(1, $min = 0, $max = 100),
        'operation_type' => $faker->randomElements(['idraulic', 'painting', 'electric', 'mechanic', 'damage']),
    ];
});

