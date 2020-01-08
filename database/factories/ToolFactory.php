<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Tool;
use Faker\Generator as Faker;

$factory->define(Tool::class, function (Faker $faker) {
    return Tool::getSemiFakeData($faker);
});
