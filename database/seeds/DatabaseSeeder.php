<?php

use App\Comment;
use App\Profession;
use App\Section;
use App\Task;
use App\TaskInterventType;
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
        $this->utils = new Utils();

        // creo un sito
        $this->command->warn(" ------ SITES --------");

        $site = $this->utils->createSite();

        $this->command->info("Site {$site->name} created");

        // crea N boat...
        $this->command->warn(" ------ BOATS & SECTIONS --------");

        $boats = [];
        for ($i = 0; $i < 5; $i++) {
            $boats[$i] = $this->utils->createBoat($site);

            $this->command->info("Boat {$boats[$i]->name} created");

            // ... e 5 sezioni per ciascuna

            $sections[$boats[$i]->id] = [];
            $left_done = $right_done = false;
            for ($s = 0; $s < 5; $s++) {

                $sections[$boats[$i]->id] = $this->utils->createSection($boats[$i]);

                $this->command->info("Section {$sections[$boats[$i]->id]->name} for Boat {$boats[$i]->name} created");

                // Creare un left, un right e gli altri deck
                if (!$left_done) {
                    $sections[$boats[$i]->id]->update(['section_type' => SECTION_TYPE_LEFT_SIDE]);
                    $left_done = true;
                    continue;
                }
                if (!$right_done) {
                    $sections[$boats[$i]->id]->update(['section_type' => SECTION_TYPE_RIGHT_SIDE]);
                    $right_done = true;
                    continue;
                }
                $sections[$boats[$i]->id]->update(['section_type' => SECTION_TYPE_DECK]);
            }
        }

        // creo N professioni a caso
        $this->command->warn(" ------ PROFESSIONS --------");

        $professions = [];
        for ($s = 0; $s < 20; $s++) {
            $professions[$s] = $this->utils->createProfession();

            $this->command->info("Profession {$professions[$s]->name} created");
        }

        // Creo ed associo degli utenti alle barche
        // Per ogni barca N workers, N boat manager, N backend manager
        foreach ($boats as $boat) {

            // Workers
            $this->command->warn(" ------ WORKERS FOR BOAT {$boat->name} --------");

            $workers = [];
            for ($s = 0; $s < 15; $s++) {
                $worker = $this->utils->createUser(ROLE_WORKER);
                $profession = $this->faker->randomElement($professions);
                $this->utils->associateUserToBoat($worker, $boat, $profession);

                $this->command->info("Worker {$worker->name} for Boat {$boat->name}, with Profession {$profession->name} created");
                $workers[] = $worker;
            }

            // Boat Managers
            $this->command->warn(" ------ BOAT MANAGERS FOR BOAT {$boat->name} --------");

            $boat_managers = [];
            for ($s = 0; $s < 8; $s++) {
                $bo_man = $this->utils->createUser(ROLE_BOAT_MANAGER);
                $profession = $this->faker->randomElement($professions);
                $this->utils->associateUserToBoat($bo_man, $boat, $profession);

                $this->command->info("Boat Manager {$bo_man->name} for Boat {$boat->name}, with Profession {$profession->name} created");
                $boat_managers[] = $bo_man;
            }

            // Backend Managers
            $this->command->warn(" ------ BACKEND MANAGERS FOR BOAT {$boat->name} --------");

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
            $this->command->warn(" ------ PROJECTS FOR BOAT {$boat->name} --------");

            $open = $closed = 0;
            for ($p = 0; $p < 3; $p++) {
                $project = $this->utils->createProject($site, $boat);

                $this->command->info("Project {$project->name} for Boat {$boat->name}, created");
                $projects[] = $project;

                // ...con N task associati
                $this->command->warn(" ------ TASKS FOR PROJECT {$project->name} --------");

                $intervent_types = \Config::get('storm.startup.task_intervent_types');

                for ($t = 0; $t < $this->faker->numberBetween(20, 50); $t++) {
                    $section = $this->faker->randomElement($boat->sections);
                    $intervent_type = TaskInterventType::firstOrCreate($this->faker->randomElement($intervent_types));
                    $task = $this->utils->createTask($project, $section, null, null, $intervent_type);
                    $this->command->info("Task {$task->name} for Project {$project->name}, created");

                    $this->command->warn(" ------ COMMENTS FOR TASK {$task->name} --------");
                    foreach ($workers as $user) {
                        for ($c = 0; $c < $this->faker->numberBetween(10, 30); $c++) {
                            $comment = Comment::firstOrCreate(['body' => $this->faker->sentence(10)]);
                            // associo i commenti agli autori
                            $comment->author()->associate($user)->save();
                            // ...e al task
                            $task->comments()->save($comment);
                        }
                    }

                    // accoppio la sezione al progetto
                    $this->command->warn(" ------ SECTIONS FOR PROJECT {$project->name} --------");
                    $this->utils->associateSectionToProject($section, $project);
                }

                $this->command->warn(" ------ USERS FOR PROJECT {$project->name} --------");

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

                if (0) {
                    // Uno solo deve essere open, due closed, gli altri operational
                    if ($open == 0) {
                        $open++;
                        $project->update(['project_status' => PROJECT_STATUS_IN_SITE]);
                        continue;
                    }
                    if ($closed < 2) {
                        $closed++;
                        $project->update(['project_status' => PROJECT_STATUS_CLOSED]);
                        continue;
                    }
                    $project->update(['project_status' => PROJECT_STATUS_OPERATIONAL]);
                }

                if ($open == 0) {
                    $open++;
                    $project->update(['project_status' => $this->faker->randomElement([PROJECT_STATUS_IN_SITE, PROJECT_STATUS_OPERATIONAL])]);
                    continue;
                }
                $project->update(['project_status' => PROJECT_STATUS_CLOSED]);
            }
        }
    }
}
