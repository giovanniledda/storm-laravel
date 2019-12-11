<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'producer' => $faker->company,
        'sv_percentage' => $faker->randomFloat(3),
        'components' => $faker->words(3),
    ];
});
