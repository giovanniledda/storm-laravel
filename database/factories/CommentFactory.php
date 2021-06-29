<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Comment;
use Faker\Generator as Faker;

$autoIncrement = StormUtils::autoIncrement();

$factory->define(Comment::class, function (Faker $faker) use ($autoIncrement) {
    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'body' => $faker->sentence(10),
    ];
});
