<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\ProductUseInfoBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductUseInfoBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductUseInfoBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return ProductUseInfoBlock::getSemiFakeData($faker);
    }
}
