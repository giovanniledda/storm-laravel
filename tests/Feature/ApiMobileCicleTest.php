<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Permission;
use App\Models\Profession;
use App\Models\Project;
use App\Role;
use App\Models\Section;
use App\Models\Site;
use App\Models\User;
use Laravel\Passport\Passport;
use const PERMISSION_ADMIN;
use const PERMISSION_BOAT_MANAGER;
use const PERMISSION_WORKER;
use const ROLE_ADMIN;
use const ROLE_BACKEND_MANAGER;
use const ROLE_BOAT_MANAGER;
use const ROLE_WORKER;
use Tests\TestApiCase;

/*
 * Questo test verifica il ciclo tipo dell'applicazione mobile.
 * 1) ws #A01 che l'utente boot manager possa effettuare il login - API Mobile -
 * 2) ws #B03 inserimento della barca -  API utilizzata dal backoffice -
 * 3) ws #B06 accoppia utente alla barca -  API utilizzata dal backoffice -
 * 4) ws #B01 visualizza solo le barche assegnate con #B03 - API Mobile -
 * 5) ws #S01 visualizza le sezioni per la barca selezionata - API Mobile -
 * 6) ws #T05 inserisce un task - API Mobile -
 * 7) ws #T01 visualizza i task della sezione ( ponte ) selezionata - API Mobile -
 */

class ApiMobileCicleTest extends TestApiCase
{
    public $boat =
         [
            'data'=>[
                    'type' => 'boats',
                    'attributes'=>[
                                        'name'=> 'Teliri',
                                        'registration_number'=> '9105889',
                                         'manufacture_year'=>'1996',
                                         'length'=>'115.5',
                                         'draft'=>'19',
                                         'beam'=>'9',
                                         'boat_type'=>'M/Y',
                            ],
                ],
         ];

    // https://docs.spatie.be/laravel-permission/v2/advanced-usage/unit-testing/
    public $token = '';

    /**
     * ws #A01 che l'utente boot manager possa effettuare il login - API Mobile -
     */
    public function test_01_A01()
    {
        $this->_getToken(ROLE_BOAT_MANAGER, PERMISSION_BOAT_MANAGER);
    }

    /**
     * ws #B03 inserimento della barca -  API utilizzata dal backoffice -
     */
    public function test_02_B03()
    {
        $token = $this->_getToken(ROLE_WORKER, PERMISSION_WORKER);

        $this->refreshApplication();  // Fa una sorta di pulizia della cache perché dopo la prima post, poi tutte le chiamate successive tornano sulla stessa route

        $response = $this->json('POST', route('api:v1:boats.create'), $this->boat, [
            'Authorization' => 'Bearer '.$token,
             'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);

        $response->assertStatus(201);
    }

    /**
     *  ws #B06 accoppia utente alla barca -  API utilizzata dal backoffice -
     */
    public function test_03_B06()
    {
        $this->refreshApplication();  // Fa una sorta di pulizia della cache perché dopo la prima post, poi tutte le chiamate successive tornano sulla stessa route

        $this->_populateProfessions();
        $token = $this->_getToken(ROLE_ADMIN, PERMISSION_ADMIN);
        $response = $this->json('POST', route('api:v1:boats.create'), $this->boat, [
            'Authorization' => 'Bearer '.$token,
             'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);
        $response->assertStatus(201);

        $boat = \App\Models\Boat::find(1);

        $data =
         [
            'data'=>[
                    'type' => 'boat-users',
                    'attributes'=>[
                       'profession_id' => 1,
                       'user_id'=> 1,
                       'boat_id'=> $boat->id,
                            ],
                ],
         ];
        $this->refreshApplication();

        $response = $this->json('POST', route('api:v1:boat-users.create'), $data, [
            'Authorization' => 'Bearer '.$token,
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);

        $response->assertStatus(201);
    }

    /*
     * ws #B01 visualizza solo le barche assegnate con #B03 - API Mobile -
     */
    public function test_04_B01()
    {
        $this->refreshApplication();  // Fa una sorta di pulizia della cache perché dopo la prima post, poi tutte le chiamate successive tornano sulla stessa route
        $token = $this->_getToken(ROLE_BOAT_MANAGER, ROLE_BOAT_MANAGER);

        $response = $this->json('GET', route('api:v1:boats.index'), [], [
        'Authorization' => 'Bearer '.$token,
           'Content-type' => 'application/vnd.api+json',
           'Accept' => 'application/vnd.api+json',
            ]);
        $response->assertStatus(200);
    }

    /**
     * #S01 visualizza le sezioni per la barca selezionata - API Mobile -
     */
    public function test_05_S01()
    {
        $this->refreshApplication();

        $token = $this->_getToken(ROLE_ADMIN, PERMISSION_ADMIN);
        $response = $this->json('POST', route('api:v1:boats.create'), $this->boat, [
            'Authorization' => 'Bearer '.$token,
             'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);
        $response->assertStatus(201);

        $boat = \App\Models\Boat::find(1);

        $this->_createSections($boat);
        $this->refreshApplication();

        $token = $this->_getToken(ROLE_BOAT_MANAGER, ROLE_BOAT_MANAGER);
        $response = $this->json('GET', route('api:v1:boats.relationships.sections', ['id'=> 1]), [], [
        'Authorization' => 'Bearer '.$token,
           'Content-type' => 'application/vnd.api+json',
           'Accept' => 'application/vnd.api+json',
            ]);
        $response->assertStatus(200);
    }

    /**
     * ws #T05 inserisce un task - API Mobile -
     * ws #T01 visualizza i task della sezione ( ponte ) selezionata - API Mobile -
     */
    public function test_06_T05_T01()
    {
        $this->refreshApplication();
        $token = $this->_getToken(ROLE_BOAT_MANAGER, ROLE_BOAT_MANAGER);
        $site_name = $this->faker->sentence;
        $site = new Site([
                'name' => $site_name,
                'lat' => $this->faker->randomFloat(2, -60, 60),
                'lng' => $this->faker->randomFloat(2, -60, 60),
            ]
        );
        $site->save();

        $boat = \App\Models\Boat::create(['name'=> 'Teliri',
                                        'registration_number'=> '9105889',
                                         'manufacture_year'=>'1996',
                                         'length'=>'115.5',
                                          'draft'=>'19',
                                         'beam'=>'9', ]);
        $this->_createSections($boat);

        $project = \App\Models\Project::create(
                [
                    'name'=>'rebuildproject',
                    'project_status'=>'open',
                    ]
                );
        $project->site()->associate($site)->save();
        $project->boat()->associate($boat)->save();

        $this->assertEquals($site->name, $project->site->name);
        $this->_populateInterventType();

        $data = [
            'data'=> [
        'type'=> 'tasks',
        'attributes'=> [
            'description'=>'description',
            'estimated_hours'=>'2.23',
            'worked_hours'=>'2.2',
            'for_admins'=>'1',
            'boat_id'=>'1',
            'project_id'=>'1',
            'section_id'=>'1',
            'author_id'=>'1',
            'x_coord'=>'2.744',
            'y_coord'=>'2.98798',
            'intervent_type_id'=>'1',
        ],
    ], ];

        $response = $this->json('POST', route('api:v1:tasks.create'), $data, [
            'Authorization' => 'Bearer '.$token,
             'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);
        $response->assertStatus(201);

        $response = $this->json('GET', route('api:v1:tasks.index'), [
             'filter[section_id]'=>1,
             'filter[project_id]'=>1,

         ], [
            'Authorization' => 'Bearer '.$token,
             'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            ]);
        $response->assertStatus(200);
    }

    /**
     * private functions
     */

    /**
     * crea un utente
     * @param type $ruolo
     * @return User
     */
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

    /** associa la barca all'utente */
    private function _boatAssociate(User $user, Boat $boat)
    {
        $boat->associatedUsers()
            ->create(['profession_id' => 1, 'boat_id' => $boat->id, 'user_id' => $user->id])
            ->save();
        $this->assertDatabaseHas('boat_user', ['boat_id' => $boat->id, 'user_id' => $user->id]);
    }

    /**
     * popola le professioni
     */
    private function _populateProfessions()
    {
        $professions = ['owner', 'chief engineer', 'captain', 'ship\'s boy'];
        foreach ($professions as $profession) {
            $prof = Profession::create(['name'=>$profession]);
            $prof->save();
        }
    }

    private function _populateInterventType()
    {
        $interventTypes = ['flat', 'flot'];
        foreach ($interventTypes as $interventType) {
            $type = \App\Models\TaskInterventType::create([
                'name'=>$interventType,
                    ]);
            $type->save();
        }
    }

    private function _getToken($UserRole, $UserPermission):string
    {
        $role = Role::firstOrCreate(['name' => $UserRole]);
        $permission = Permission::firstOrCreate(['name' => $UserPermission]);
        $user = $this->_addUser($UserRole); // equipaggio ??
        $user->givePermissionTo($permission);
        /*** test connessione con l'utente Admin */
        $token = $this->_grantTokenPassword($user);
        $this->assertIsString($token);

        $this->json('GET', route('api.auth.user'), [], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'type', 'attributes']]);

        return $token;
    }

    private function _createSections($boat)
    {
        $this->_createDeck(
                [
                    'name'=>'Deck 1',
                    'section_type'=>'deck',
                    'position'=>0,
                    'code'=>'D1',
                    'boat_id' => $boat->id,
                ]);
        $this->_createDeck(
                [
                    'name'=>'Deck 2',
                    'section_type'=>'deck',
                    'position'=>1,
                    'code'=>'D2',
                    'boat_id' => $boat->id,
                ]);
        $this->_createDeck(
                [
                    'name'=>'Deck 3',
                    'section_type'=>'deck',
                    'position'=>2,
                    'code'=>'D3',
                    'boat_id' => $boat->id,
                ]);
        $this->_createDeck(
                [
                    'name'=>'Deck 4',
                    'section_type'=>'deck',
                    'position'=>3,
                    'code'=>'D3',
                    'boat_id' => $boat->id,
                ]);
    }

    private function _createDeck($deck)
    {
        $d = Section::create($deck);
        $d->save();
    }
}
