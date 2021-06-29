<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\Subsection;
use App\Utils\Utils;
use Illuminate\Database\Eloquent\Factories\Factory;

$autoIncrement = Utils::autoIncrement();

class SubsectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subsection::class;

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
            'storm_id' => $this->faker->randomNumber(4),
            'comment' => $this->faker->text(140),
        ];
    }
}
