<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(4),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'project_type' => $this->faker->randomElement([PROJECT_TYPE_NEWBUILD, PROJECT_TYPE_REFIT]),
            'acronym' => $this->faker->word,
            'project_status' => $this->faker->randomElement(PROJECT_STATUSES),
        ];
    }
}
