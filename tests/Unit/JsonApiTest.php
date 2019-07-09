<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Project;
use App\Boat;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class JsonApiTest extends TestCase
{

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

        $response = $this->json('POST', route('api:v1:projects.create'), $data, $headers)
            ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        $project_id = $content['data']['id'];

        $project = Project::find($project_id);

        $this->assertEquals($project->id, $project_id);
    }


    function test_get_project_and_his_boat()
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

        $project->boat()->associate($boat)->save();

        $this->assertDatabaseHas('projects', ['name' => $project_name]);

        $data = [];

        $headers = [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ];

        $response = $this->json('GET', route('api:v1:projects.read', ['record' => $project->id]), $data, $headers)
            ->assertJsonStructure(['data' => ['attributes' => ['boatid']]]);

        $content = json_decode($response->getContent(), true);

        $boat_id = $content['data']['attributes']['boatid'];

        $boat = Boat::find($boat_id);

        $this->assertEquals($boat->id, $project->boat->id);
    }


}