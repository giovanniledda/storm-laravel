<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Subsection;
use App\Utils\Utils;
use Faker\Generator as Faker;

$autoIncrement = Utils::autoIncrement();

$factory->define(Subsection::class, function (Faker $faker) use ($autoIncrement)  {

    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->sentence(3),
        'storm_id' => $faker->randomNumber(4),
        'comment' => $faker->text(140),
    ];
});
