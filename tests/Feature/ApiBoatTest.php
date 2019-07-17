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

class ApiBoatTest extends TestApiCase
{

    /**
     * QUESTO TEST :
     * CREA 4 Utenti con ruoli diversi nell'applicazione
     * verifica che l'admin veda tutte le barche
     * verifica che il boat manager veda tutte le barche
     * verifica che tutti gli altri utenti veda solo le barche che ha assegnato
     */

    private $roles = ['admin', 'bootmanager', 'worker'];
    /** crea un utente e lo associa al ruolo */

    function test_all() {
        // crep o ruoli
        foreach($this->roles as $role) {
           $roles[] = Role::create(['name' => $role]);

        }

        $adminPerm = Permission::create(['name' => 'admin']);
        $bootmanagerPerm = Permission::create(['name' => 'bootmanager']);
        $workerPerm = Permission::create(['name' => 'worker']);

        $admin1       = $this->addUser($roles[0]);//admin

        $admin1->givePermissionTo($adminPerm);
        $admin1->givePermissionTo($bootmanagerPerm);
        $admin1->givePermissionTo($workerPerm);

        $boatManager1 = $this->addUser($roles[1]);//boat manager
        $boatManager2 = $this->addUser($roles[1]);//boat manager
        $user         = $this->addUser($roles[2]);//user

        /** creo tre barche */
        $boat1 = $this->createBoat();
        $boat2 = $this->createBoat();
        $boat3 = $this->createBoat();
        /** associo la barca1 ad other e bootmanager1 */
        $this->boatApiAssociate($admin1, $user, $boat1);
        $this->boatApiAssociate($admin1, $boatManager1, $boat1);

        $this->boatApiAssociate($admin1,$boatManager2, $boat2);
        $this->boatApiAssociate($admin1,$user, $boat2);
/*
        /*** test connessione con l'utente User */
   //     $tokenUser = $this->UserAuthenticatedRequest($user);
         /*** test connessione con l'utente Admin */
   //     $tokenAdmin = $this->UserAuthenticatedRequest($admin1);
        /*** test connessione con l'utente bootManager1 */
   //     $tokenbootManager1 = $this->UserAuthenticatedRequest($bootManager1);
        /*** test connessione con l'utente bootManager2 */
   //     $tokenbootManager2 = $this->UserAuthenticatedRequest($bootManager2);
/*
        /** test api lista delle barche User */
      /*  $response = $this->json('GET',
              route('api:v1:boats.index'), [], ['Authorization' => 'Bearer '.$tokenUser]);
*/


        /// deve vedere tutte e tre le boat

        $this->getBoatList($boatManager1, 1);
    }

    private function getBoatList(User $user, int $expeted) {
        Passport::actingAs($user);
        $r = $this->json('GET', route('api:v1:projects.index'), [], $this->headers);
       /// $r = $this->json('GET', route('api:v1:boats.index'), [], $this->headers);
        $r->assertStatus(200);
        $re = json_decode($r->getContent(), true);
        $c =   count($re['data']);
        $this->logResponce($r);
        $this->assertEquals( $expeted,   $c);

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

 /** associa la barca all'utente via api*/
 private function boatApiAssociate(User $connectedUser, User $user, Boat $boat) {
     $data = [
         'data'=>[
             'type'=> 'boat-users',
             'attributes' =>['role'=>'commander', 'boat_id'=>$boat->id ,'user_id'=>$user->id]
             ]
        ];
     Passport::actingAs($connectedUser);
     $response = $this->json('POST', route('api:v1:boat-users.create'), $data, $this->headers);

     $this->logResponce($response);
     $this->assertDatabaseHas('boat_user', [ 'boat_id'=>$boat->id,'user_id'=>$user->id ]);
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
