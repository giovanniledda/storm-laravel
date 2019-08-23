<?php

use App\Profession;
use App\Section;
use App\Task;
use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;
use App\User;
use App\Boat;
use App\Site;
use App\Project;
use Faker\Factory as Faker;
use Seeds\SeederUtils as Utils;

class DatabaseSeeder extends Seeder
{
    protected $faker;
    protected $utils;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $this->utils = new Utils($this->faker);

        // creo un sito
        $site = $this->utils->createSite();
        $this->command->info("Site {$site->name} created");

        // crea 10 boat...
        $boats = [];
        for ($i = 0; $i < 10; $i++) {
            $boats[$i] = $this->utils->createBoat($site);
            $this->command->info("Boat {$boats[$i]->name} created");

            // ... e 5 sezioni per ciascuna
            $sections[$boats[$i]->id] = [];
            for ($s = 0; $s < 5; $s++) {
                $sections[$boats[$i]->id] = $this->utils->createSection($boats[$i]);
                $this->command->info("Section {$sections[$boats[$i]->id]->name} for Boat {$boats[$i]->name} created");
            }
        }

        // creo N professioni a caso
        $professions = [];
        for ($s = 0; $s < $this->faker->randomDigitNotNull(); $s++) {
            $professions[$s] = $this->utils->createProfession();
            $this->command->info("Profession {$professions[$s] ->name} created");
        }

        // Creo ed associo degli utenti alle barche
        // Per ogni barca N workers, N boat manager, N backend manager
        foreach ($boats as $boat) {

            // Workers
            $workers = [];
            for ($s = 0; $s < 15; $s++) {
                $worker = $this->utils->createUser(ROLE_WORKER);
                $profession = $this->faker->randomElement($professions);
                $this->utils->associateUserToBoat($worker, $boat, $profession);

                $this->command->info("Worker {$worker->name} for Boat {$boat->name}, with Profession {$profession->name} created");
                $workers[] = $worker;
            }

            // Boat Managers
            $boat_managers = [];
            for ($s = 0; $s < 8; $s++) {
                $bo_man = $this->utils->createUser(ROLE_BOAT_MANAGER);
                $profession = $this->faker->randomElement($professions);
                $this->utils->associateUserToBoat($bo_man, $boat, $profession);

                $this->command->info("Boat Manager {$bo_man->name} for Boat {$boat->name}, with Profession {$profession->name} created");
                $boat_managers[] = $bo_man;
            }

            // Backend Managers
            $backend_managers = [];
            for ($s = 0; $s < 4; $s++) {
                $be_man = $this->utils->createUser(ROLE_BACKEND_MANAGER);
                $profession = $this->faker->randomElement($professions);
                $this->utils->associateUserToBoat($be_man, $boat, $profession);

                $this->command->info("Backend Manager {$be_man->name} associated to Boat {$boat->name}, with Profession {$profession->name} created");
                $backend_managers[] = $be_man;
            }


            // per ogni boat creo N progetti...
            $projects = [];
            for ($s = 0; $s < 3; $s++) {
                // todo: uno solo deve essere open
                $project = $this->utils->createProject($site, $boat);
                $this->command->info("Project {$project->name} for Boat {$boat->name}, created");
                $projects[] = $project;

                // ...con N task associati
                $tasks = [];
                for ($s = 0; $s < 4; $s++) {
                    $section = $this->faker->randomElement($boat->sections);
                    $task = $this->utils->createTask($project, $section, null, null, $this->utils->createTaskInterventType());
                    $this->command->info("Task {$task->name} for Project {$project->name}, created");
                }

                // al progetto assegno tutti i BE manager
                foreach ($backend_managers as $backend_manager) {
                    $profession = $this->faker->randomElement($professions);
                    $this->utils->associateUserToProject($backend_manager, $project, $profession);

                    $this->command->info("Backend Manager {$backend_manager->name} associated to Project {$project->name}, with Profession {$profession->name} created");
                }

                // al progetto assegno tutti i BO manager
                foreach ($boat_managers as $boat_manager) {
                    $profession = $this->faker->randomElement($professions);
                    $this->utils->associateUserToProject($boat_manager, $project, $profession);

                    $this->command->info("Boat Manager {$boat_manager->name} associated to Project {$project->name}, with Profession {$profession->name} created");
                }

                // al progetto assegno alcuni dei Workers
                foreach ($this->faker->randomElements($workers, 8) as $worker) {
                    $profession = $this->faker->randomElement($professions);
                    $this->utils->associateUserToProject($worker, $project, $profession);

                    $this->command->info("Worker {$worker->name} associated to Project {$project->name}, with Profession {$profession->name} created");
                }

            }

        }

    }

}
