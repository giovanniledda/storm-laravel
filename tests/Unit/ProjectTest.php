<?php

namespace Tests\Unit;

use App\Site;
use App\Project;
use Tests\TestCase;
use App\Storm\StormProject;
use App\Storm\StormSite;

class ProjectTest extends TestCase
{

    function test_can_create_project_without_site(){
        $fake_name = $this->faker->sentence;

        $project = new \App\Project([
            'name' => $fake_name
        ]
        );

        $project->save();  
        
        $this->assertDatabaseHas('projects', ['name' =>  $fake_name] );
    }

    function test_can_create_project_related_to_site(){
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

        $project->site()->save($site);

        $this->assertDatabaseHas('projects', ['name' =>  $project_name] );

        $related_site =  $project->site;
        $this->assertEquals($site->name, $related_site->name);

    }

    function test_can_create_project_related_to_item(){
        $item_name = $this->faker->sentence;
        $item = new \App\Item([
            'name' => $item_name
        ]
        );
        $item->save();

        $project_name = $this->faker->sentence;
        $project = new \App\Project([
            'name' => $project_name
        ]
        );
        $project->save();  

        $project->item()->save($item);

        $this->assertDatabaseHas('projects', ['name' =>  $project_name] );

        $related_item =  $project->item;
        $this->assertEquals($item->name, $related_item->name);

        $this->assertEquals($project->id, $item->itemable->id);

    }

    function test_can_create_storm_project_related_to_storm_site()
    {

        $storm_site_name = $this->faker->sentence;
        $site = new StormSite([
                'name' => $storm_site_name
            ]
        );
        $site->save();

        $storm_project_name = $this->faker->sentence;
        $project = new StormProject([
                'name' => $storm_project_name
            ]
        );
        $project->save();

        $project->site()->save($site);

        $this->assertDatabaseHas('storm_projects', ['name' => $storm_project_name]);

        $related_site = $project->site();
//        $this->assertInstanceOf(Site::class, $related_site);

//        $this->assertEquals($site->name, $related_site->name);
    }

    

}