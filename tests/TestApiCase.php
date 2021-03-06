<?php

namespace Tests;

use App\Boat;
use App\Project;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Laravel\Passport\ClientRepository;

//abstract class TestApiCase extends BaseTestCase
abstract class TestApiCase extends TestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected $faker;
    private $log = false; // pushare con fase

    protected $headers = [
        'Content-type' => 'application/vnd.api+json',
        'Accept' => 'application/vnd.api+json',
    ];

    public function setUp(): void {
        parent::setUp();
        // // To test Oauth Grants
//         \Artisan::call('passport:install',['-vvv' => true]);
        //  Passport::actingAs(factory(User::class)->create());
    }

    public function logResponse(\Illuminate\Foundation\Testing\TestResponse $response) {
        if ($this->log) {
            echo "\nStatusCode : ".$response->getStatusCode();
            echo "\nResponse : ".$response->getContent();
            echo "\n";
        }
    }


    public function _grantTokenPassword(User $user, $password = null)
    {

        $oauth_client = $this->_createTestPasswordGrantClient($user);

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' => $user->email,
            'password' => $password ? $password : 'fake123',
            'scope' => '',
        ];

        //Send post request

        $response = $this->json('POST', route('passport.token'), $data_ok);
        $token = $response->json()['access_token'];

        return ($token) ? $token : null;
    }

    /**
     * Utility function: creates a Password Grant Token Client
     */
    public function _createTestPasswordGrantClient(User $user)
    {
        $clientRepository = new ClientRepository();
        $clientRepository->createPasswordGrantClient($user->id, \Config::get('auth.token_clients.password.name'), '/');

        $oauth_client_id = \Config::get('auth.token_clients.password.id');
        return $clientRepository->find($oauth_client_id);
    }

    public function _addUser($ruolo): User
    {
        $user_data = [
            'name' => $this->faker->firstNameMale,
            'email' => $this->faker->email,
            'password' => 'fake123',
            'c_password' => 'fake123',
        ];
        $user = User::create($user_data);
        $user->assignRole($ruolo);
        return $user;
    }

    public function _testUserConnection($user, $password = null)
    {
        /*** test connessione con l'utente $user */
        $token = $this->_grantTokenPassword($user, $password);
        $this->assertIsString($token);
        Passport::actingAs($user);

        $response = $this->json('GET', route('api.auth.user'), [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);

        $this->logResponse($response);
    }

    /* crea un nuovo task dato il progetto */
    public function createProjectTask(Project $project): Task
    {
        $task = factory(Task::class)->create();
        $task->project()->associate($project)->save();
        return $task;
    }

    /* crea un progetto con la barca relazionata */
    public function createBoatAndHisProject(): array
    {
        $boat = factory(Boat::class)->create();
        $project = factory(Project::class)->create();
        $project->boat()->associate($boat)->save();
        return ['boat' => $boat, 'project' => $project];
    }
}
