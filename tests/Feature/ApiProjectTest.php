<?php

namespace Tests\Feature;

use Tests\TestApiCase;

use App\Project;
use App\Boat;
use App\Task;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiProjectTest extends TestApiCase
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
         $this->assertEquals( 200, $response->getStatusCode()); /// prima il valore che ti aspetti poi quello da controllare
        // possiamo anche scrivere cosi : $response->assertStatus(200);
        $this->logResponce($response);
    }
    /* crea un progetto e la sua boat e assegna 10 tasks
       testa la rotta api/v1/projects/{record}/relationships/task
    */
    function test_get_project_and_tasks() {
        $projectAndBoat = $this->createBoatAndHisProject();
        $projectAndBoat['project'];
        for ($i=0; $i < 10; $i++) {
            $this->createProjectTask($projectAndBoat['project']);
        }
        $response = $this->json('GET',
            route('api:v1:projects.relationships.tasks.read',
                    ['record' => $projectAndBoat['project']->id]),
                    [],
                    $this->headers);
                    $response->assertStatus(200);

     $this->logResponce($response);
    }

    /* crea un nuovo task dato il progetto */
    private function createProjectTask(\App\Project $project) : \App\Task {

        $task_title = $this->faker->sentence;
        $task = new Task([
                'title' => $task_title,
                'description' => $this->faker->text,
            ]
        );
        $task->save();
        $project->tasks()->save($task);
        return $task;
    }

    /* crea un progetto con la barca relazionata */
    private function createBoatAndHisProject() : array {
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
        return ['boat'=>$boat, 'project'=>$project];
    }



}
