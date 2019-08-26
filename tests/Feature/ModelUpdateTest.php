<?php

namespace Tests\Feature;

use App\Boat;
use App\Jobs\NotifyTaskUpdates;
use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Permission;
use App\Project;
use App\Profession;
use App\Role;
use App\Task;
use function count;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use App\User;

use const PERMISSION_BOAT_MANAGER;
use const ROLE_BOAT_MANAGER;

class ModelUpdateTest extends TestCase
{
    function test_can_create_notifications_related_to_task_creation()
    {
        // Creo professioni
        $professions = factory(Profession::class, 5)->create();

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
        $users = factory(User::class, 10)->create();
        $this->assertNotCount(0, $users);

        foreach ($users as $user) {
            // ruoli e permessi ad utente
            $role->givePermissionTo(PERMISSION_BOAT_MANAGER);
            $user->assignRole(ROLE_BOAT_MANAGER);
            $this->assertTrue($user->can(PERMISSION_BOAT_MANAGER));

            // associo utente al progetto
            $prof = $this->faker->randomElement($professions);
            $user->projects()->attach($project->id, ['profession_id' => $prof->id]);
            $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $user->id, 'profession_id' => $prof->id]);
        }

        // Creo i task e li assegno al progetto

        $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();

//        $project->tasks()->saveMany($tasks);  // Vedi mail di Ledda del 24 luglio: se uso questa poi $t->project è null :-(

        foreach ($tasks as $t) {
            $this->assertInstanceOf(Task::class, $t);

            $t->project()->associate($project)->save();
            $this->assertNotNull($t->project->id);
            $this->assertEquals($t->project_id, $project->id);

            $task_users = $t->getUsersToNotify();
            $this->assertCount(count($users), $task_users);
        }

        if (\Config::get('queue.default') == 'database') {

            $this->assertDatabaseHas('jobs', ['queue' => 'default']);

            // i job di notifica devono essere il doppio dei task (uno parte per la creazione e l'altro per l'update)
            $counts = DB::table('jobs')->count();
            $this->assertEquals($counts, count($tasks)*2);

        } else {

            // verifico che gli utenti abbiano le notifiche
            foreach ($users as $user) {
                $this->assertNotCount(0, $user->notifications);
                $this->assertCount($user->unreadNotifications->count(), $user->notifications);
                foreach ($user->notifications as $notification) {

                    $this->assertThat($notification->type,
                        $this->logicalOr(
                            'App\Notifications\TaskCreated',  // Se uso TaskCreated::class ottengo il paradosso: Failed asserting that 'App\Notifications\TaskUpdated' is instance of class "App\Notifications\TaskCreated" or is instance of class "App\Notifications\TaskUpdated".
                            'App\Notifications\TaskUpdated'
                        ));
                }
            }
        }


    }
    
}
