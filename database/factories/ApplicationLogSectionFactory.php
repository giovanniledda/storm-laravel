<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\ApplicationLogSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationLogSectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApplicationLogSection::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ApplicationLogSection::getSemiFakeData($faker);
    }
}
