<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\ApplicationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApplicationLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ApplicationLog::getSemiFakeData($faker);
    }
}
