<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\Boat;
use App\Utils\Utils;
use Illuminate\Database\Eloquent\Factories\Factory;

$autoIncrement = StormUtils::autoIncrement();

class BoatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Boat::class;

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
            'name' => $this->faker->sentence(3),
            'registration_number' => $this->faker->randomNumber(5),
            'flag' => $this->faker->country(),
            'manufacture_year' => $this->faker->year(),
            'length' => $this->faker->randomFloat(4, 1, 200),
            'draft' => $this->faker->randomFloat(4, 1, 40),
            'beam' => $this->faker->randomFloat(4, 1, 3),
            'boat_type' => $this->faker->randomElement([BOAT_TYPE_MOTOR, BOAT_TYPE_SAIL]),
        ];
    }
}
