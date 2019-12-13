<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Product;
use App\Zone;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return Product::getSemiFakeData($faker);
});
