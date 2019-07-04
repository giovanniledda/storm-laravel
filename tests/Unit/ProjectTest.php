<?php

namespace Tests\Unit;

use App\Site;
use App\Project;
use App\Item;
use Tests\TestCase;
use App\Storm\StormProject;
use App\Storm\StormSite;

class ProjectTest extends TestCase
{

    function test_can_create_project_without_site()
    {
        $fake_name = $this->faker->sentence;

        $project = new \App\Project([
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

        $project->site()->save($site);
        $related_site = $project->site()->first();
        $this->assertEquals($site->name, $related_site->name);

    }

    function test_can_create_project_related_to_item()
    {
        $item_name = $this->faker->sentence;
        $item = new Item([
                'name' => $item_name
            ]
        );
        $item->save();

        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name
            ]
        );
        $project->save();


        $this->assertDatabaseHas('projects', ['name' => $project_name]);

        $project->item()->save($item);
        $related_item = $project->item()->first();
        $this->assertEquals($item->name, $related_item->name);
        $this->assertEquals($project->id, $item->itemable->id);
    }

    function test_can_create_storm_project_related_to_site()
    {

        $storm_site_name = $this->faker->sentence;
        $site = new Site([
                'name' => $storm_site_name
            ]
        );
        $site->save();

        $storm_project_name = $this->faker->sentence;
        $s_project = Project::create(StormProject::class, [
            'name' => $storm_project_name,
            'type' => 'refit',
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
        ]);

        $s_project->site()->save($site);

        $related_site = $s_project->site()->first();

        $this->assertEquals($site->name, $related_site->name);
    }


}