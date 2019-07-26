<?php

namespace Tests\Feature;

use Laravel\Passport\Passport;
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
        $boat = factory(Boat::class)->create();

        $fake_name = $this->faker->sentence;
        $data = [
            'data' => [
                'attributes' => [
                    'name' => $fake_name,
                    'boat_id' => $boat->id,
                ],
                'type' => 'projects',
            ]
        ];

        $headers = [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ];

        /*** connessione con l'utente Admin */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

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
        $boat = factory(Boat::class)->create();

        $project = factory(Project::class)->create();

        $this->assertDatabaseHas('projects', ['name' => $project->name]);

        $project->boat()->associate($boat)->save();

        $data = [];

        /*** connessione con l'utente Admin */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $response = $this->json('GET', route('api:v1:projects.read', ['record' => $project->id]), $data, $this->headers)
            ->assertJsonStructure(['data' => ['attributes' => ['boat_id']]]);

        $content = json_decode($response->getContent(), true);

        $boat_id = $content['data']['attributes']['boat_id'];

        $boat = Boat::find($boat_id);

        $this->assertEquals($boat->id, $project->boat->id);
        $this->logResponce($response);
    }

    /** get projects collections */
    function test_get_projects_collection()
    {

        for ($i = 0; $i < 10; $i++) {
            $this->createBoatAndHisProject();
        }

        /*** connessione con l'utente Admin */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $response = $this->json('GET', route('api:v1:projects.index'), [], $this->headers);
        $this->assertEquals(200, $response->getStatusCode()); /// prima il valore che ti aspetti poi quello da controllare
        // possiamo anche scrivere cosi : $response->assertStatus(200);
        $this->logResponce($response);
    }

    /* crea un progetto e la sua boat e assegna 10 tasks
       testa la rotta api/v1/projects/{record}/relationships/task
    */
    function test_get_project_and_tasks()
    {
        $projectAndBoat = $this->createBoatAndHisProject();
        $projectAndBoat['project'];
        for ($i = 0; $i < 10; $i++) {
            $this->createProjectTask($projectAndBoat['project']);
        }

        /*** connessione con l'utente Admin */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $response = $this->json('GET',
            route('api:v1:projects.relationships.tasks.read',
                ['record' => $projectAndBoat['project']->id]),
            [],
            $this->headers);
        $response->assertStatus(200);

        $this->logResponce($response);
    }

    /* crea un nuovo task dato il progetto */
    private function createProjectTask(\App\Project $project): \App\Task
    {

        $task = factory(Task::class)->create();
        $task->project()->associate($project)->save();
        return $task;
    }

    /* crea un progetto con la barca relazionata */
    private function createBoatAndHisProject(): array
    {
        $boat = factory(Boat::class)->create();
        $project = factory(Project::class)->create();
        $project->boat()->associate($boat)->save();
        return ['boat' => $boat, 'project' => $project];
    }


}
