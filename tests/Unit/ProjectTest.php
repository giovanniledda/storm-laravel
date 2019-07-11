<?php

namespace Tests\Unit;

use App\Site;
use App\Project;
use App\Boat;
use Tests\TestCase;

class ProjectTest extends TestCase
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
                'name' => $site_name
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
                'name' => $boat_name
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

}
