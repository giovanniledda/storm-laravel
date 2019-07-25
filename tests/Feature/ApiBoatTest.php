<?php

namespace Tests\Feature;

use App\Boat;
use App\User;
use App\Permission;
use App\Role;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

use const ROLE_ADMIN;
use const ROLE_BOAT_MANAGER;
use const ROLE_WORKER;
use const PERMISSION_ADMIN;
use const PERMISSION_BOAT_MANAGER;
use const PERMISSION_WORKER;
use Tests\TestApiCase;


class ApiBoatTest extends TestApiCase
{

    // https://docs.spatie.be/laravel-permission/v2/advanced-usage/unit-testing/
    public function setUp(): void
    {
        // first include all the normal setUp operations
        parent::setUp();

        // now re-register all the roles and permissions
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    }

    /**
     * QUESTO TEST :
     * CREA 4 Utenti con ruoli diversi nell'applicazione
     * verifica che l'admin veda tutte le barche
     * verifica che il boat manager veda tutte le barche
     * verifica che tutti gli altri utenti veda solo le barche che ha assegnato
     */

    /** crea un utente e lo associa al ruolo */

    function test_all()
    {
        $admin_role = Role::firstOrCreate(['name' => ROLE_ADMIN]);
        $boat_manager_role = Role::firstOrCreate(['name' => ROLE_BOAT_MANAGER]);
        $worker_role = Role::firstOrCreate(['name' => ROLE_WORKER]);

        $adminPerm = Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $bootmanagerPerm = Permission::firstOrCreate(['name' => PERMISSION_BOAT_MANAGER]);
        $workerPerm = Permission::firstOrCreate(['name' => PERMISSION_WORKER]);

        $admin_role->givePermissionTo($adminPerm);
        $admin_role->givePermissionTo($bootmanagerPerm);
        $admin_role->givePermissionTo($workerPerm);

        $admin1 = $this->addUser(ROLE_ADMIN);
        $boatManager1 = $this->addUser(ROLE_BOAT_MANAGER);
        $boatManager2 = $this->addUser(ROLE_BOAT_MANAGER);
//        $user = $this->addUser(ROLE_WORKER);


        /*** test connessione con l'utente Admin */
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);


        /** creo tre barche */
//        $boat1 = $this->createBoat();
//        $boat2 = $this->createBoat();

        /** associo la barca1 ad other e bootmanager1 */
//        $this->boatApiAssociate($admin1, $boatManager1, $boat1);

        /** associo la barca2 ad other e bootmanager2 */
//        $this->boatApiAssociate($admin1, $boatManager2, $boat2);


        /** creo N barche e le associo a $boatManager1 */
        $boats_for_bm1 = [];
        for ($i = 0; $i <= $this->faker->randomDigitNotNull(); $i++) {
            $boats_for_bm1[$i] = $this->createBoat();
            $this->boatApiAssociate($admin1, $boatManager1, $boats_for_bm1[$i]);
        }
        $this->assertNotCount(0, $boats_for_bm1);

        /** creo N barche e le associo a $boatManager2 */
        $boats_for_bm2 = [];
        for ($i = 0; $i <= $this->faker->randomDigitNotNull(); $i++) {
            $boats_for_bm2[$i] = $this->createBoat();
            $this->boatApiAssociate($admin1, $boatManager2, $boats_for_bm2[$i]);
        }
        $this->assertNotCount(0, $boats_for_bm2);

        // deve vedere SOLO le sue boat
        /*** test connessione con l'utente $boatManager1 */
        $token = $this->_grantTokenPassword($boatManager1);
        $this->assertIsString($token);

        $this->getBoatList($boatManager1, count($boats_for_bm1));
    }

    private function getBoatList(User $user, int $expected)
    {
        // manca qualcosa del genere:
        $data = [
            'data' => [
                'type' => 'boat-users',
                'attributes' => [
                    'user_id' => $user->id
                ]
            ]
        ];
        Passport::actingAs($user);
        $r = $this->json('GET', route('api:v1:boats.index'), $data, $this->headers);
        $r->assertStatus(200);
        $re = json_decode($r->getContent(), true);
        $c = count($re['data']);
        $this->logResponce($r);
        $this->assertEquals($expected, $c);
    }

    private function addUser($ruolo): User
    {
        $user_data = [
            'name' => $this->faker->firstNameMale,
            'email' => $this->faker->email,
            'password' => 'fake123',
            'c_password' => 'fake123',
        ];
        $user = User::create($user_data);
        // commentato perché non sta funzionando:  neanche con questo https://docs.spatie.be/laravel-permission/v2/advanced-usage/unit-testing/
//        $user->assignRole($ruolo);
        return $user;
    }

    /** associa la barca all'utente via api*/
    private function boatApiAssociate(User $connectedUser, User $user, Boat $boat)
    {
        $data = [
            'data' => [
                'type' => 'boat-users',
                'attributes' => ['role' => 'commander', 'boat_id' => $boat->id, 'user_id' => $user->id]
            ]
        ];
        Passport::actingAs($connectedUser);
        $response = $this->json('POST', route('api:v1:boat-users.create'), $data, $this->headers);

//        $this->logResponce($response);
        $this->assertDatabaseHas('boat_user', ['boat_id' => $boat->id, 'user_id' => $user->id]);
    }

    /** associa la barca all'utente */
    private function boatAssociate(User $user, Boat $boat)
    {
        $boat->associatedUsers()
            ->create(['role' => 'commander', 'boat_id' => $boat->id, 'user_id' => $user->id])
            ->save();
        $this->assertDatabaseHas('boat_user', ['boat_id' => $boat->id, 'user_id' => $user->id]);
    }

    /** crea una barca */
    private function createBoat(): Boat
    {
        $boat = factory(Boat::class)->create();
        $this->assertInstanceOf(Boat::class, $boat);
        $this->assertDatabaseHas('boats', ['name' => $boat->name]);
        return $boat;
    }

    public function _grantTokenPassword(User $user)
    {

        $oauth_client = $this->_createTestPasswordGrantClient($user);

        //User's data
        $data_ok = [
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
            'username' => $user->email,
            'password' => 'fake123',
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
    private function _createTestPasswordGrantClient(User $user)
    {
        $clientRepository = new ClientRepository();
        $clientRepository->createPasswordGrantClient($user->id, \Config::get('auth.token_clients.password.name'), '/');

        $oauth_client_id = \Config::get('auth.token_clients.password.id');
        return $clientRepository->find($oauth_client_id);
    }

}
