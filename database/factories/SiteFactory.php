<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Site;
use Faker\Generator as Faker;

$autoIncrement = StormUtils::autoIncrement();

$factory->define(Site::class, function (Faker $faker) use ($autoIncrement) {
    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        //
    ];
});
