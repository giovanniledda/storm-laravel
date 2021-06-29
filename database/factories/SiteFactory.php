<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

$autoIncrement = StormUtils::autoIncrement();

class SiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Site::class;

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
            //
        ];
    }
}
