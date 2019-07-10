<?php

namespace Tests\Unit;

use Tests\TestApiCase;

use App\Project;
use App\Boat;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProjectJsonApiTest extends TestApiCase
{

    /** create **/
    function test_can_create_project()
    {

        $this->disableExceptionHandling();

        $fake_name = $this->faker->sentence;
        $data = [
            'data' => [
                'attributes' => [
                    'name' => $fake_name,
                ],
                'type' => 'projects',
            ]
        ];

        $headers = [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ];

        $response = $this->json('POST', route('api:v1:projects.create'), $data, $this->headers)
            ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        $project_id = $content['data']['id'];
        $project = Project::find($project_id);
        $this->assertEquals($project->id, $project_id);
        $this->logResponce($response);
    }

    /** create get entity */
    function test_get_project_and_his_boat()
    {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number'=> $this->faker->sentence($nbWords = 1)
            ]
        );
        $boat->save();

        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name
            ]
        );

        $project->boat()->associate($boat)->save();

        $this->assertDatabaseHas('projects', ['name' => $project_name]);

        $data = [];

        $response = $this->json('GET', route('api:v1:projects.read', ['record' => $project->id]), $data, $this->headers)
            ->assertJsonStructure(['data' => ['attributes' => ['boatid']]]);

        $content = json_decode($response->getContent(), true);

        $boat_id = $content['data']['attributes']['boatid'];

        $boat = Boat::find($boat_id);

        $this->assertEquals($boat->id, $project->boat->id);
        $this->logResponce($response);
    }

    /** get projects collections */
    function test_get_projects_collection() {
         for ($i=0; $i < 10; $i++) {
            $this->createBoatAndHisProject();
        }

        $response = $this->json('GET', route('api:v1:projects.index'), [], $this->headers);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->logResponce($response);
    }


    function createBoatAndHisProject() {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number'=> $this->faker->sentence($nbWords = 1)
            ]
        );
        $boat->save();

        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name
            ]
        );
        $project->boat()->associate($boat)->save();
    }
}
