<?php

namespace Tests\Feature;

use Laravel\Passport\Passport;
use Tests\TestApiCase;
use App\Boat;
use App\Permission;
use App\Project;
use App\Profession;
use App\Role;
use App\Task;
use App\User;

use const PERMISSION_BOAT_MANAGER;
use const ROLE_BOAT_MANAGER;

class ApiUpdateTest extends TestApiCase
{
    function test_can_create_notifications_related_to_task_creation()
    {
        $this->_populateProfessions();
        // Creo barca
        $boat = factory(Boat::class)->create();

        // Creo progetto e lo associo alla barca
        $project = factory(Project::class)->create();
        $project->boat()->associate($boat)->save();

        // creo ruoli e permessi BOAT (in futuro potremmo dover limitare le notifiche in base a questi)
        $role = Role::firstOrCreate(['name' => ROLE_BOAT_MANAGER]);
        $permission = Permission::firstOrCreate(['name' => PERMISSION_BOAT_MANAGER]);

        // Creo utenti da assegnare al progetto
        $users = factory(User::class, $this->faker->randomDigitNotNull)->create();

        foreach ($users as $user) {
            // ruoli e permessi ad utente
            $role->givePermissionTo(PERMISSION_BOAT_MANAGER);
            $user->assignRole(ROLE_BOAT_MANAGER);

            // associo utente al progetto
            $user->projects()->attach($project->id, [ 'profession_id' => 1]);
        }

        // Devo "loggare" un utente altrimenti il TaskObserver si incazza
        $this->_testUserConnection($users[0], USER_FAKE_PASSWORD);
//        $this->refreshApplication();  // Fa una sorta di pulizia della cache perché dopo la prima post, poi tutte le chiamate successive tornano sulla stessa route

        // Creo i task e li assegno al progetto
        $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();

        foreach ($tasks as $t) {
            $t->project()->associate($project)->save();
//            $task_users = $t->getUsersToNotify();
        }

        // verifico che gli utenti abbiano le notifiche
        foreach ($users as $user) {

            $this->_testUserConnection($user, USER_FAKE_PASSWORD);
            $this->refreshApplication();  // Fa una sorta di pulizia della cache perché dopo la prima post, poi tutte le chiamate successive tornano sulla stessa route
            Passport::actingAs($user);
            $response = $this->json('GET', 'api/v1/updates', [], $this->headers)
                ->assertStatus(200)
                ->assertJsonStructure(['data']);

            $notifications = $response->json()['data'];

            // devo avere tante notifiche per utente quanti sono i task
            $this->assertNotCount(0, $notifications);
            $this->assertCount(count($tasks), $notifications);

//            dd($r);
        }
    }
    
     private function _populateProfessions() {
        $professions = ['owner','chief engineer', 'captain', 'ship\'s boy'];
        foreach ($professions as $profession) {
            $prof = Profession::create(['name'=>$profession]);
            $prof->save();
        } 
        
    }
}
