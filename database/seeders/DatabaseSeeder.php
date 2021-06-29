<?php

namespace Database\Seeders;

use App\Comment;
use App\TaskInterventType;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Net7\Documents\Document;
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
        $this->command->warn(' ------ SITES --------');

        $site = $this->utils->createSite();

        $site->setName('Cantieri Benetti Livorno')
            ->setLocation('Via Edda Fagni, 1, 57100 – Livorno, Italy')
            ->setLat(43.5430829)
            ->setLng(10.2994663)
            ->save();

        $this->command->info("Site {$site->name} created");

        $site2 = $this->utils->createSite();

        $site2->setName('Cantieri Net7')
            ->setLocation('Via Giusti, 1, 56123 – Pisa, Italy')
            ->setLat(44.5430829)
            ->setLng(11.2994663)
            ->save();

        $this->command->info("Site {$site2->name} created");

        // crea N boat...
        $this->command->warn(' ------ BOATS & SECTIONS --------');

        $boats = [];
        for ($i = 0; $i < 5; $i++) {
            $boats[$i] = $this->utils->createBoat();

            // aggiungo delle immagini alle barche in base al loro tipo
            // 'boat_type' = [BOAT_TYPE_SAIL, BOAT_TYPE_MOTOR]
            $num = $this->faker->randomElement(['1', '2', '3', '4']);
            if ($boats[$i]->boat_type == BOAT_TYPE_SAIL) {
//                $this->utils->addImageToBoat($boats[$i], "./boat/sail$num.jpg", Document::GENERIC_IMAGE_TYPE);
                $this->utils->addImageToBoat($boats[$i], './boat/generic_sail_img.png', Document::GENERIC_IMAGE_TYPE);
            } else {
//                $this->utils->addImageToBoat($boats[$i], "./boat/motor$num.jpg", Document::GENERIC_IMAGE_TYPE);
                $this->utils->addImageToBoat($boats[$i], './boat/generic_motor_img.png', Document::GENERIC_IMAGE_TYPE);
            }

            $this->command->info("Boat {$boats[$i]->name} created");

            // ... e 5 sezioni per ciascuna

            $sections[$boats[$i]->id] = [];
            $left_done = $right_done = false;
            for ($s = 0; $s < 5; $s++) {
                $sections[$boats[$i]->id] = $this->utils->createSection($boats[$i]);

                $this->command->info("Section {$sections[$boats[$i]->id]->name} for Boat {$boats[$i]->name} created");

                // Creare un left, un right e gli altri deck
                if (! $left_done) {
                    $sections[$boats[$i]->id]->update([
                        'section_type' => SECTION_TYPE_LEFT_SIDE,
                        'name' => 'Starboard', ]);

                    // associo la foto di SX
                    $this->utils->addImageToSection($sections[$boats[$i]->id], './section/starboard.png');

                    $left_done = true;
                    continue;
                }
                if (! $right_done) {
                    $sections[$boats[$i]->id]->update([
                        'section_type' => SECTION_TYPE_RIGHT_SIDE,
                        'name' => 'Port', ]);

                    // associo la foto di DX
                    $this->utils->addImageToSection($sections[$boats[$i]->id], './section/port.png');

                    $right_done = true;
                    continue;
                }
                $name = $this->faker->randomElement(['sundeck', 'upperdeck', 'maindeck', 'lowerdeck', 'topdeck', 'wheelhousedeck']);
                $sections[$boats[$i]->id]->update([
                    'section_type' => SECTION_TYPE_DECK,
                    'name' => ucfirst($name), ]);

                // associo la foto di un ponte a caso
                $this->utils->addImageToSection($sections[$boats[$i]->id], "./section/$name.png");
            }
        }

        // creo N professioni a caso
        $this->command->warn(' ------ PROFESSIONS --------');

        $professions = [];
        $professions[0] = $this->utils->createProfession('owner');
        for ($s = 1; $s < 20; $s++) {
            $professions[$s] = $this->utils->createProfession('worker');

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

            $open = $closed = $imported = 0;
            for ($p = 0; $p < 3; $p++) {
                $project = $this->utils->createProject($site, $boat);

                $this->command->info("Project {$project->name} for Boat {$boat->name}, created");
                $projects[] = $project;

                // ...con N task associati
                $this->command->warn(" ------ TASKS FOR PROJECT {$project->name} --------");

                $intervent_types = Config::get('storm.startup.task_intervent_types');

                for ($t = 0; $t < $this->faker->numberBetween(1, 20); $t++) {
                    $section = $this->faker->randomElement($boat->sections);
                    $intervent_type = TaskInterventType::firstOrCreate($this->faker->randomElement($intervent_types));

                    $author = $this->faker->randomElement($boat_managers);
                    $task = $this->utils->createTask($project, $section, null, $author, $intervent_type);

                    // le coordinate fake del task cambiano in base alla tipologia di sezione
                    if ($section->section_type == SECTION_TYPE_DECK) {
                        $task->setMinX(600)->setMaxX(2500)->setMinY(1000)->setMaxY(14000)->updateXYCoordinates($this->faker);
                    } else {
                        $task->setMinX(500)->setMaxX(2000)->setMinY(2000)->setMaxY(13000)->updateXYCoordinates($this->faker);
                    }

                    // cambio la data di creazione
                    $proj_start = $project->start_date;
                    $creation_date = $this->faker->dateTimeBetween($proj_start, '+2 years');
                    $task->update(['created_at' => $creation_date]);

                    // cambio la data del primo history
                    $first_history = $task->history()->first();
                    $first_history->update(['event_date' => $creation_date]);

                    // associo qualche foto
                    $this->utils->addImageToTask($task, './task/photo1.jpg', Document::DETAILED_IMAGE_TYPE);
                    $this->utils->addImageToTask($task, './task/photo2.jpg', Document::DETAILED_IMAGE_TYPE);
                    $this->utils->addImageToTask($task, './task/photo3.jpg', Document::DETAILED_IMAGE_TYPE);
                    $this->utils->addImageToTask($task, './task/photo4.jpg', Document::DETAILED_IMAGE_TYPE);
                    $this->utils->addImageToTask($task, './task/photo5.jpg', Document::ADDITIONAL_IMAGE_TYPE);

                    // se il task è chiuso, lo stato non può essere diverso da COMPLETED o DECLINED
                    if (! $task->is_open) {
                        $task->update(['task_status' => $this->faker->randomElement([TASKS_STATUS_COMPLETED, TASKS_STATUS_DENIED])]);
                    }
                    // se il task è di stato MONITORED, deve essere aperto
                    if ($task->task_status == TASKS_STATUS_MONITORED) {
                        $task->update(['is_open' => 1]);
                    }

                    $this->command->info("Task {$task->name} for Project {$project->name}, created");

                    if ($task->status != TASKS_STATUS_DRAFT) {
                        $this->command->warn(" ------ COMMENTS FOR TASK {$task->name} --------");
                        for ($c = 0; $c < $this->faker->numberBetween(1, 5); $c++) {
                            $comment = Comment::firstOrCreate(['body' => $this->faker->sentence(10)]);
                            // associo i commenti agli autori
                            $user = $this->faker->randomElement($workers);
                            $comment->author()->associate($user)->save();
                            // ...e al task
                            $task->comments()->save($comment);
                        }
                    }

                    // accoppio la sezione al progetto
//                    $this->command->warn(" ------ SECTIONS FOR PROJECT {$project->name} --------");
//                    $this->utils->associateSectionToProject($section, $project);
                    unset($section);
                    $this->utils->print_mem();
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
                if ($imported == 0) {
                    $imported++;
                    $project->update(['imported' => 1]);
                }
                unset($project);
                $this->utils->print_mem();
            }
            unset($boat);
            $this->utils->print_mem();
        }
    }
}
