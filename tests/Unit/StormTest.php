<?php

namespace Tests\Unit;

use App\Project;
use App\Storm\StormSite;
use App\Storm\StormProject;
use App\Storm\StormBoat;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StormTest extends TestCase
{


    function test_can_create_boat_related_to_project_related_to_site()
    {
        $site_name = $this->faker->sentence;
        $s_site = new StormSite([
                'name' => $site_name
            ]
        );
        $s_site->save();

        $project_name = $this->faker->sentence;
        $s_project = Project::create(StormProject::class, [
            'name' => $project_name,
            'type' => 'refit',
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
        ]);
        $s_project->site()->save($s_site);

        $boat_name = $this->faker->sentence;
        $s_boat = new StormBoat([
                'name' => $boat_name
            ]
        );
        $s_boat->save();
        $s_boat->project()->save($s_project);

        $this->assertDatabaseHas('projects', ['name' => $project_name]);

        $related_site = $s_project->site()->first();
//        $this->assertEquals($s_site->name, $related_site->name);

        // get the inverse relation of morphOne() in project
        // this is a StormBoat in this case, but could be some other model if the relation was
        // created with a different model
//        $related_boat = $s_project->projectable()->first();
//        $this->assertEquals($s_boat->name, $related_boat->name);
//        $this->assertEquals($s_project->projectable->id, $s_boat->id);
    }


}