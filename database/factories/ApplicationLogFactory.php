<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ApplicationLog;
use Faker\Generator as Faker;

$factory->define(ApplicationLog::class, function (Faker $faker) {
    return ApplicationLog::getSemiFakeData($faker);
});
