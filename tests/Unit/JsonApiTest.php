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


    function test_get_project_and_his_item(){
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

        $data = [];
     
         $headers = [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ];

        $response = $this->json('GET', route('api:v1:projects.read', ['record' => $project->id ]), $data, $headers);
        
        $response->assertJsonStructure(['data'=>['attributes'=>['boatid']]]) ;

        /*
        var_dump($response);
        $content = json_decode($response->getContent(), true);

        $this->assertEquals('', $content);;
        $this->assertJsonStructure(['boatid' => 1], 
        */
/*
       $content = json_decode($response->getContent(), true);

        $project_id = $content['data']['id'];

        $project = \App\Project::find($project_id);

        $this->assertEquals($project->id, $project_id);
*/

    }


}