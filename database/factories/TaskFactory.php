<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Task;
use Faker\Generator as Faker;

$autoIncrement = StormUtils::autoIncrement();

$factory->define(Task::class, function (Faker $faker) use ($autoIncrement) {

    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'number' => $faker->randomDigitNotNull(),
        'title' => $faker->sentence(),
        'description' => $faker->text(),
        'estimated_hours' => $faker->randomFloat(1, $min = 0, $max = 100),
        'worked_hours' => $faker->randomFloat(1, $min = 0, $max = 100),
    ];
});

