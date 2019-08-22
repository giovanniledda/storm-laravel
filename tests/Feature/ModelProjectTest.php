<?php

namespace Tests\Feature;

use App\Site;
use App\Project;
use App\Boat;
use App\Task;
use Tests\TestCase;
use Faker\Provider\Base as fakerBase;

class ModelProjectTest extends TestCase
{

    function test_can_create_project_without_site()
    {
        $project = factory(Project::class)->create();
        $this->assertDatabaseHas('projects', ['name' => $project->name]);
    }

    function test_can_create_project_related_to_site()
    {
        $site_name = $this->faker->sentence;
        $site = new Site([
                'name' => $site_name,
                'lat' => $this->faker->randomFloat(2, -60, 60),
                'lng' => $this->faker->randomFloat(2, -60, 60)
            ]
        );
        $site->save();

        $project = factory(Project::class)->create();

        $this->assertDatabaseHas('projects', ['name' => $project->name]);

        $project->site()->associate($site)->save();

        $this->assertEquals($site->name, $project->site->name);

    }

    function test_can_create_project_related_to_boat()
    {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number' => $this->faker->sentence($nbWords = 1),
                'length'  => $this->faker->randomFloat(2, 12, 110),
                'draft'  => $this->faker->randomFloat(2, 2, 15),
                "boat_type"=>"M/Y"
            ]
        );
        $boat->save();

        $project = factory(Project::class)->create();

        $this->assertDatabaseHas('projects', ['name' => $project->name]);

        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->name, $project->boat->name);
    }


}
