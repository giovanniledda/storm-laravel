<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ProductUseInfoBlock;
use Faker\Generator as Faker;

$factory->define(ProductUseInfoBlock::class, function (Faker $faker) {
    return ProductUseInfoBlock::getSemiFakeData($faker);
});
