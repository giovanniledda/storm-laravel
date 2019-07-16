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
        $fake_name = $this->faker->sentence;
        $project = new Project([
                'name' => $fake_name
            ]
        );

        $project->save();

        $this->assertDatabaseHas('projects', ['name' => $fake_name]);
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

        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name
            ]
        );
        $project->save();

        $this->assertDatabaseHas('projects', ['name' => $project_name]);

        $project->site()->associate($site)->save();

        $this->assertEquals($site->name, $project->site->name);

    }

    function test_can_create_project_related_to_boat()
    {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number' => $this->faker->sentence($nbWords = 1)
            ]
        );
        $boat->save();

        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name
            ]
        );
        $project->save();

        $this->assertDatabaseHas('projects', ['name' => $project_name]);

        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->name, $project->boat->name);
    }


    function test_can_create_project_related_to_subsection()
    {
        // TODO...
    }

}
