<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Zone;
use Faker\Generator as Faker;

$factory->define(Zone::class, function (Faker $faker) {
    return Zone::getSemiFakeData($faker);
});
