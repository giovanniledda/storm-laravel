<?php

namespace Tests\Feature;

use App\Boat;
use App\Permission;
use App\Project;
use App\Role;
use App\Task;
use Tests\TestCase;
use App\User;

use const PERMISSION_BOAT_MANAGER;
use const ROLE_BOAT_MANAGER;

class ModelUpdateTest extends TestCase
{
    function test_can_create_notifications_related_to_task_creation()
    {
        // Creo barca
        $boat = factory(Boat::class)->create();

        // Creo progetto e lo associo alla barca
        $project = factory(Project::class)->create();
        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->id, $project->boat->id);

        // creo ruoli e permessi BOAT (in futuro potremmo dover limitare le notifiche in base a questi)
        $role = Role::firstOrCreate(['name' => ROLE_BOAT_MANAGER]);
        $permission = Permission::firstOrCreate(['name' => PERMISSION_BOAT_MANAGER]);

        // Creo utenti da assegnare al progetto
        $users = factory(User::class, $this->faker->randomDigitNotNull)->create();

        foreach ($users as $user) {
            // ruoli e permessi ad utente
            $role->givePermissionTo(PERMISSION_BOAT_MANAGER);
            $user->assignRole(ROLE_BOAT_MANAGER);
            $this->assertTrue($user->can(PERMISSION_BOAT_MANAGER));

            // associo utente al progetto
            $user->projects()->attach($project->id, ['role' => PROJECT_USER_ROLE_OWNER]);
            $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $user->id]);
        }

        // Creo i task e li assegno al progetto
        $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();
        $project->tasks()->saveMany($tasks);

        foreach ($tasks as $t) {
            $this->assertEquals($t->project_id, $project->id);
        }


    }
}
