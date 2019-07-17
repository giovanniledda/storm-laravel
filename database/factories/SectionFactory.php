<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Section;
use App\Utils\Utils;
use Faker\Generator as Faker;

$autoIncrement = Utils::autoIncrement();

$factory->define(Section::class, function (Faker $faker) use ($autoIncrement)  {

    $autoIncrement->next();
    $sections_types = [SECTION_NAME_LEFT_SIDE, SECTION_NAME_RIGHT_SIDE, SECTION_NAME_DECK];

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->randomElement(['Left Side', 'Right Side', $faker->numerify('Deck #')]),
        'type' => $faker->randomElement($sections_types),
        'position' => $faker->randomDigitNotNull(),
        'code' => $faker->lexify('???-???')
    ];
});


