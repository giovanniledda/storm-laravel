<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class JsonApiTest extends TestCase
{

    function test_can_create_project(){
        
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

        $response = $this->json('POST', route('api:v1:projects.create'), $data, $headers);

        $content = json_decode($response->getContent(), true);

        $project_id = $content['data']['id'];

        $project = \App\Project::find($project_id);

        $this->assertEquals($project->id, $project_id);


    }
}