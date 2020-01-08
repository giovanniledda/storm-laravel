<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\DetectionsInfoBlock;
use Faker\Generator as Faker;

$factory->define(DetectionsInfoBlock::class, function (Faker $faker) {
    return DetectionsInfoBlock::getSemiFakeData($faker);
});
