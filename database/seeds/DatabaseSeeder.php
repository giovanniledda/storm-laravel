<?php

use App\Task;
use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;
use App\User;
use App\Boat;
use App\Site;
use App\Project;


use Faker\Factory;

class DatabaseSeeder extends Seeder
{
    protected $faker;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Factory::create();
        // creo un sito

        $site_name = $this->faker->sentence;
        $site = new Site([
            'name' => $site_name,
            'lat' => $this->faker->randomDigitNotNull,
            'lng' => $this->faker->randomDigitNotNull,
        ]);
        $site->save();

        /** cerca tutti gli utenti inseriti nel precedente seeder*/
        $boats = [];
        /* creo 10 barche e le assegno agli utenti */
        for ($i = 0; $i < 10; $i++) {
            $boats[$i] = $this->createBoat($site);
            $this->command->info("Boat {$boats[$i]->name} created");
        }

        /**** creo i progetti ********/


        $users = User::all();

        foreach ($users as $user) {
            // WORKER
            if ($user->hasRole(ROLE_WORKER)) {

                $this->boatAssociate($user, $boats[0], $role = 'commander');
                $this->boatAssociate($user, $boats[1], $role = 'commander');
                $this->boatAssociate($user, $boats[2], $role = 'commander');
                $this->boatAssociate($user, $boats[3], $role = 'commander');
                $this->boatAssociate($user, $boats[4], $role = 'commander');
            }
            // BACKEND_MANAGER
            if ($user->hasRole(ROLE_BACKEND_MANAGER)) {
                $this->boatAssociate($user, $boats[0], $role = 'commander');
            }
            // BOAT_MANAGER
            if ($user->hasRole(ROLE_BOAT_MANAGER)) {
                $this->boatAssociate($user, $boats[4], $role = 'commander');
                $this->boatAssociate($user, $boats[5], $role = 'commander');
            }
            // ADMIN
            if ($user->hasRole(ROLE_ADMIN)) {
                // assegno gli altri permessi
                $user->givePermissionTo(PERMISSION_BACKEND_MANAGER);
                $user->givePermissionTo(PERMISSION_BOAT_MANAGER);
                $user->givePermissionTo(PERMISSION_WORKER);
                /* all'utente ADMIN non assegno barche, le dovrebbe vedere tutte.*/
            }


        }
//        $project = $this->createProject($site, $boats[0]);
//        $this->createTasksAndAssociateWithProject($project);

        $projects = $this->createManyProjectsAndAssociateWithSiteAndBoats($site, $boats);
        foreach ($projects as $project) {
            $this->createTasksAndAssociateWithProject($project);
        }
    }


    private function createTasksAndAssociateWithProject($project = null)
    {
        do {
            try {
                $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();
        //        $project->tasks()->saveMany($tasks);  // Vedi mail di Ledda del 24 luglio: se uso questa poi $t->project è null :-(
                $created = true;

            } catch (Exception $e) {
                $created = false;
            }
        } while (!$created);

        if (isset($tasks)) {
            foreach ($tasks as $t) {
                if ($project) {
                    $t->project()->associate($project)->save();
                }
            }
            return $tasks;
        }

        return [];
    }


    private function createProject($site, $boat)
    {
        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name,
                'site_id' => $site->id
            ]
        );
        $project->save();
        $project->boat()->associate($boat)->save();
        //$project->site()->associate($site)->save();

        return $project;
    }


    private function createManyProjectsAndAssociateWithSiteAndBoats($site, $boats)
    {
        $all_projects = [];
        if (!empty($boats)) {
            foreach ($boats as $boat) {
                do {
                    try {
                        $projects = factory(Project::class, $this->faker->randomDigitNotNull)->create();
                        $projs_created = true;

                    } catch (Exception $e) {
                        $projs_created = false;
                    }
                } while (!$projs_created);

                if (isset($projects)) {
                    foreach ($projects as $project) {
                        $project->boat()->associate($boat)->save();
                        $project->site()->associate($site)->save();
                        $all_projects[] = $project;
                    }
                }
            }
        }

        return $all_projects;
    }

    private function boatAssociate(User $user, Boat $boat, $role = 'commander')
    {
        $boat->associatedUsers()
            ->create(['role' => $role, 'boat_id' => $boat->id, 'user_id' => $user->id])
            ->save();
    }

    private function createBoat($site): Boat
    {

        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number' => $this->faker->randomDigitNotNull,
                'site_id' => $site->id
            ]
        );
        $boat->save();
        return $boat;
    }


}
