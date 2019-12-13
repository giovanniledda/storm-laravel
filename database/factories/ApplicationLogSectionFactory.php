<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ApplicationLogSection;
use Faker\Generator as Faker;

$factory->define(ApplicationLogSection::class, function (Faker $faker) {
    return ApplicationLogSection::getSemiFakeData($faker);
});
