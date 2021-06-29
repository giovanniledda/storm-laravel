<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Profession;

$autoIncrement = StormUtils::autoIncrement();

class ProfessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profession::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $autoIncrement->next();

        return [
            'id' => $autoIncrement->current(),
            'name' => $this->faker->jobTitle,
        ];
    }
}
