<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Permission;
use App\Models\Project;
use App\Role;
use App\Models\Site;
use Laravel\Passport\Passport;
use const PERMISSION_ADMIN;
use const ROLE_ADMIN;
use Tests\TestApiCase;

class ApiProjectTest extends TestApiCase
{
    /** create **/
    public function test_can_create_project()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);

        $this->disableExceptionHandling();
        $boat = Boat::factory()->create();
        $site_name = $this->faker->sentence;
        $site = new Site([
                'name' => $site_name,
                'lat' => $this->faker->randomFloat(2, -60, 60),
                'lng' => $this->faker->randomFloat(2, -60, 60),
            ]
        );
        $site->save();
        $fake_name = $this->faker->sentence;
        $data = [
            'data' => [
                'attributes' => [
                    'name' => $fake_name,
                    'boat_id' => $boat->id,
                    'project_type'=>  'newbuild',
                     'site_id'=> $site->id,
                ],
                'type' => 'projects',
            ],
        ];

        // creo ruoli e permessi BOAT (in futuro potremmo dover limitare le notifiche in base a questi)

        /*** connessione con l'utente Admin */
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        //  $this->assertStringContainsString($token_admin);
        Passport::actingAs($admin1);
        $response = $this->json('POST', route('api:v1:projects.create'), $data, [
            'Authorization' => 'Bearer '.$token_admin,
             'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);
        //  ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        $project_id = $content['data']['id'];
        $project = Project::find($project_id);
        $this->assertEquals($project->id, $project_id);
        $this->logResponse($response);
    }

    /** create get entity */
    public function test_get_project_and_his_boat()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $boat = Boat::factory()->create();
        $project = Project::factory()->create();
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
        $this->logResponse($response);
    }

    /** get projects collections */
    public function test_get_projects_collection()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);

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
        $this->logResponse($response);
    }

    /* crea un progetto e la sua boat e assegna 10 tasks
       testa la rotta api/v1/projects/{record}/relationships/task
    */
    public function test_get_project_and_tasks()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);

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

        $this->logResponse($response);
    }
}
