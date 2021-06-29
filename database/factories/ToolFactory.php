<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

class ToolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return Tool::getSemiFakeData($faker);
    }
}
