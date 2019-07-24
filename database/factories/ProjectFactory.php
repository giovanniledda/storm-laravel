<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Project;
use Faker\Generator as Faker;

$autoIncrement = StormUtils::autoIncrement();

$factory->define(Project::class, function (Faker $faker) use ($autoIncrement) {

    $autoIncrement->next();

    return [
        'id' => $autoIncrement->current(),
        'name' => $faker->sentence(4),
        'start_date' => $faker->date(),
        'end_date' => $faker->date(),
        'project_type' => $faker->randomElement([PROJECT_TYPE_NEWBUILD, PROJECT_TYPE_REFIT]),
        'acronym' => $faker->word,
        'project_status' => $faker->randomElement(PROJECT_STATUSES),
    ];
});
