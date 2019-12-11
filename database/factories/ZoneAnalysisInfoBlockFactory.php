<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ZoneAnalysisInfoBlock;
use Faker\Generator as Faker;

$factory->define(ZoneAnalysisInfoBlock::class, function (Faker $faker) {
    return ZoneAnalysisInfoBlock::getSemiFakeData($faker);
});
