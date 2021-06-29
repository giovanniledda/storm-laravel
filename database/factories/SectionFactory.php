<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\Section;
use App\Utils\Utils;
use Illuminate\Database\Eloquent\Factories\Factory;

$autoIncrement = Utils::autoIncrement();

class SectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Section::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $autoIncrement->next();
        $sections_types = [SECTION_TYPE_LEFT_SIDE, SECTION_TYPE_RIGHT_SIDE, SECTION_TYPE_DECK];

        return [
            'id' => $autoIncrement->current(),
            'name' => $this->faker->randomElement(['Left Side', 'Right Side', $this->faker->numerify('Deck #')]),
            'section_type' => $this->faker->randomElement($sections_types),
            'position' => $this->faker->randomDigitNotNull(),
            'code' => $this->faker->lexify('???-???'),
        ];
    }
}
