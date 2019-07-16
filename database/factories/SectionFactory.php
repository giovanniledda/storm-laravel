<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Section;
use Faker\Generator as Faker;

$autoIncrement = autoIncrement();


$factory->define(Section::class, function (Faker $faker) use ($autoIncrement)  {

    $autoIncrement->next();
    $sections_types = ['left_side', 'right_side', 'deck'];

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->randomElement(['Left Side', 'Right Side', $faker->numerify('Deck #')]),
        'type' => $faker->randomElement($sections_types),
        'position' => $faker->randomDigitNotNull(),
        'code' => $faker->lexify('???-???')
    ];
});


function autoIncrement()
{
    for ($i = 0; $i < 1000; $i++) {
        yield $i;
    }
}