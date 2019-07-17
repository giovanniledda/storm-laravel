<?php

namespace Tests\Feature;

use Tests\TestApiCase;

use App\Project;
use App\Boat;
use App\User;
use Laravel\Passport\ClientRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Laravel\Passport\Passport;

class BoatsJsonApiTest extends TestApiCase
{

    /**
     * QUESTO TEST :
     * CREA 4 Utenti con ruoli diversi nell'applicazione
     * verifica che l'admin veda tutte le barche
     * verifica che il boat manager veda tutte le barche
     * verifica che tutti gli altri utenti veda solo le barche che ha assegnato
     */

    private $roles = ['Admin', 'Boot Manager', 'User'];
    /** crea un utente e lo associa al ruolo */

    function test_all() {
        // crep o ruoli
        foreach($this->roles as $role) {
           $roles[] = Role::create(['name' => $role]);

        }
        $admin1       = $this->addUser($roles[0]);//admin
        $bootManager1 = $this->addUser($roles[1]);//boat manager
        $bootManager2 = $this->addUser($roles[1]);//boat manager
        $user        = $this->addUser($roles[2]);//user
        //   $permission = Permission::create(['name' => 'see other boot']);

        /** creo tre barche */
        $boat1 = $this->createBoat();
        $boat2 = $this->createBoat();
        $boat3 = $this->createBoat();
        /** associo la barca1 ad other e bootmanager1 */
        $this->boatAssociate($user, $boat1);
        $this->boatAssociate($bootManager1, $boat1);

        $this->boatAssociate($bootManager2, $boat2);
        $this->boatAssociate($user, $boat2);
/*
        /*** test connessione con l'utente User */
        $tokenUser = $this->UserAuthenticatedRequest($user);
         /*** test connessione con l'utente Admin */
        $tokenAdmin = $this->UserAuthenticatedRequest($admin1);
        /*** test connessione con l'utente bootManager1 */
        $tokenbootManager1 = $this->UserAuthenticatedRequest($bootManager1);
        /*** test connessione con l'utente bootManager2 */
        $tokenbootManager2 = $this->UserAuthenticatedRequest($bootManager2);
/*
        /** test api lista delle barche User */
      /*  $response = $this->json('GET',
              route('api:v1:boats.index'), [], ['Authorization' => 'Bearer '.$tokenUser]);
*/
        Passport::actingAs($admin1);
        $response = $this->json('GET',
              route('api.auth.user',[]),
                      [],
                      []);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);
        $this->logResponce($response);

        $response = $this->json('GET',
              route('api:v1:boats.index',[]),
                      [],
                      $this->headers);
        $response->assertStatus(200);
        /// deve vedere tutte e tre le boat
        $r = json_decode($response->getContent(), true);
        $this->assertEquals( 3,   count($r['data']));
        //->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);

        Passport::actingAs($user);
        $response = $this->json('GET',
        route('api:v1:boats.index',[]),
                [],
                $this->headers);
        $response->assertStatus(200);
        /// deve vedere 2 boat
        $r = json_decode($response->getContent(), true);
        $this->assertEquals( 2,   count($r['data']));

        Passport::actingAs($bootManager1);
        $response = $this->json('GET',

        route('api:v1:boats.index',[]),
                [],
                $this->headers);
        $response->assertStatus(200);
        /// deve vedere 1 boat
        $r = json_decode($response->getContent(), true);
        $this->assertEquals( 1,   count($r['data']));

        Passport::actingAs($bootManager2);
        $response = $this->json('GET',

        route('api:v1:boats.index',[]),
                [],
                $this->headers);
        $response->assertStatus(200);
        /// deve vedere 1 boat
        $r = json_decode($response->getContent(), true);
        $this->assertEquals( 1,   count($r['data']));
    }


    private function addUser($ruolo):User {
        $user_data = [
            'name' => $this->faker->firstNameMale,
            'email' => $this->faker->email,
            'password' => 'fake123',
            'c_password' => 'fake123',
        ];
        $user =  User::create( $user_data );
        $user->assignRole($ruolo);
        return $user;
    }



    /** associa la barca all'utente */
    private function boatAssociate(User $user, Boat $boat) {
       $boat->associatedUsers()
            ->create(['role'=>'commander', 'boat_id'=>$boat->id ,'user_id'=>$user->id])
            ->save();
        $this->assertDatabaseHas('boat_user', [ 'boat_id'=>$boat->id,'user_id'=>$user->id ]);
    }

    /** crea una barca */
    private function createBoat():Boat {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number'=> $this->faker->sentence($nbWords = 1)
            ]
        );
        $boat->save();
        $this->assertDatabaseHas('boats', [ 'name'=>$boat_name]);
        return $boat;
    }

    public function UserAuthenticatedRequest($user)
    {
       $oauth_client = $this->_createTestPasswordGrantClient($user);

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' =>  $user->email,
            'password' => 'fake123',
            'scope' => '',
        ];

        //Send post request

        $response = $this->json('POST', route('passport.token'), $data_ok) // oauth/token
            ->assertStatus(200)
            ->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);

        $token = $response->json()['access_token'];
        $response = $this->json('GET', route('api.auth.user'), [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);
        return $token;
    }

    /**
     * Utility function: creates a Password Grant Token Client
     */
    private function _createTestPasswordGrantClient(User $user)
    {
        $clientRepository = new ClientRepository();
        $clientRepository->createPasswordGrantClient($user->id, \Config::get('auth.token_clients.password.name'), '/');

        $oauth_client_id = \Config::get('auth.token_clients.password.id');
        return $clientRepository->find($oauth_client_id);
    }

}
