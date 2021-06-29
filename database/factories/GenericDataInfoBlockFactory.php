<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\GenericDataInfoBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

class GenericDataInfoBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GenericDataInfoBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return GenericDataInfoBlock::getSemiFakeData($faker);
    }
}
