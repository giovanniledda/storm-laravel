<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StormTest extends TestCase
{


    function test_can_create_boat_related_to_project_related_to_site(){
        $site_name = $this->faker->sentence;
        $site = new \App\Storm\StormSite([
            'name' => $site_name
        ]
        );
        $site->save();

        $project_name = $this->faker->sentence;
        $project = new \App\Storm\StormProject([
            'name' => $project_name
        ]
        );
        $project->save();  

        $project->site()->save($site);


        $boat_name = $this->faker->sentence;
        $boat = new \App\Storm\StormBoat([
            'name' => $boat_name
        ]
        );
        $boat->save();  
        $boat->project()->save($project);

        $this->assertDatabaseHas('projects', ['name' =>  $project_name] );

        $related_site =  $project->site;
        $this->assertEquals($site->name, $related_site->name);

        // get the inverse relation of morphOne() in project
        // this is a StormBoat in this case, but could be some other model if the relation was
        // created with a different model
        $related_boat =  $project->projectable;
        $this->assertEquals($boat->name, $related_boat->name);

        $this->assertEquals($project->projectable->id, $boat->id);


    }

    

}