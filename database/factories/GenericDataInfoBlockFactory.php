<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\GenericDataInfoBlock;
use Faker\Generator as Faker;

$factory->define(GenericDataInfoBlock::class, function (Faker $faker) {
    return GenericDataInfoBlock::getSemiFakeData($faker);
});
