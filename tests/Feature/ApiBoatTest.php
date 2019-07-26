<?php

namespace Tests\Feature;

use Tests\TestApiCase;
use App\Boat;
use App\User;
use App\Permission;
use App\Role;
use Laravel\Passport\Passport;

use const ROLE_ADMIN;
use const ROLE_BOAT_MANAGER;
use const ROLE_WORKER;
use const PERMISSION_ADMIN;
use const PERMISSION_BOAT_MANAGER;
use const PERMISSION_WORKER;


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

        $admin1 = $this->_addUser(ROLE_ADMIN);
        $boatManager1 = $this->_addUser(ROLE_BOAT_MANAGER);
        $boatManager2 = $this->_addUser(ROLE_BOAT_MANAGER);
//        $user = $this->addUser(ROLE_WORKER);


        /*** test connessione con l'utente Admin */
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);

        $response = $this->json('GET', route('api.auth.user'), [], ['Authorization' => 'Bearer '.$token_admin])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);

        $this->logResponce($response);

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


        /*** test connessione con l'utente $boatManager1 */
        $token = $this->_grantTokenPassword($boatManager1);
        $this->assertIsString($token);
        Passport::actingAs($boatManager1);

        $response = $this->json('GET', route('api.auth.user'), [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);

        $this->logResponce($response);

        // deve vedere SOLO le boat di $boatManager1
        $this->getBoatList($boatManager1, count($boats_for_bm1));
    }

    private function getBoatList(User $user, int $expected)
    {
        Passport::actingAs($user);
        $r = $this->json('GET', 'api/v1/boats', [], $this->headers);
        $r->assertStatus(200);
        $re = json_decode($r->getContent(), true);
        $c = count($re['data']);
        $this->logResponce($r);
        $this->assertEquals($expected, $c);
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


}
