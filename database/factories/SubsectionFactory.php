<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Subsection;
use Faker\Generator as Faker;

$factory->define(Subsection::class, function (Faker $faker) {

    return [
        'name' => $faker->sentence(3),
        'storm_id' => $faker->randomNumber(4),
        'comment' => $faker->text(140),
    ];
});
