<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\DetectionsInfoBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetectionsInfoBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DetectionsInfoBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return DetectionsInfoBlock::getSemiFakeData($this->faker);
    }
}
