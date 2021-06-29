<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\ZoneAnalysisInfoBlock;

class ZoneAnalysisInfoBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ZoneAnalysisInfoBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ZoneAnalysisInfoBlock::getSemiFakeData($faker);
    }
}
