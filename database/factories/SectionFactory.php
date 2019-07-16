<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Section;
use Faker\Generator as Faker;

$factory->define(Section::class, function (Faker $faker) {

    $sections_types = ['left_side', 'right_side', 'deck'];

    return [
        'name' => $faker->randomElement(['Left Side', 'Right Side', $faker->numerify('Deck #')]),
        'type' => $faker->randomElement($sections_types),
        'position' => $faker->randomDigitNotNull(),
        'code' => $faker->lexify('???-???')
    ];
});
