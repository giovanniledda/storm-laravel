<?php

namespace Database\Seeders;

use App\Comment;
use App\Project;
use App\TaskInterventType;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Net7\Documents\Document;
use Seeds\SeederUtils as Utils;

class AddTasksToProjectSeeder extends Seeder
{
    protected $faker;
    protected $utils;

    /**
     * This seeder adds some Tasks to a specific Project, whose ID will be passed in input.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '-1');

        $this->faker = Faker::create();
        $this->utils = new Utils();

        try {
            $proj_ids = Project::pluck('id')->all();
            $proj_id = $this->command->askWithCompletion('Please insert a Project ID (the first will be choosen by default)', $proj_ids, $proj_ids[0]);
            /** @var Project $project */
            $project = Project::findOrFail($proj_id);

            $this->command->info("Project {$project->name} retrived");
            $boat = $project->boat;
            $users = User::all();

            // ...con N task associati
            $this->command->warn(" ------ TASKS FOR PROJECT {$project->name} --------");

            $intervent_types = \Config::get('storm.startup.task_intervent_types');

            for ($t = 0; $t < $this->faker->numberBetween(100, 200); $t++) {
                $section = $this->faker->randomElement($boat->sections);
                $intervent_type = TaskInterventType::firstOrCreate($this->faker->randomElement($intervent_types));

                $author = $this->faker->randomElement($users);
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
                for ($ti = 1; $ti <= 4; $ti++) {
                    if ($this->faker->boolean(30)) {
                        $this->utils->addImageToTask($task, './task/photo'.$ti.'.jpg', Document::DETAILED_IMAGE_TYPE);
                    }
                }
                if ($this->faker->boolean(30)) {
                    $this->utils->addImageToTask($task, './task/photo5.jpg', Document::ADDITIONAL_IMAGE_TYPE);
                }

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
                        $user = $this->faker->randomElement($users);
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

            unset($project);
            $this->utils->print_mem();
        } catch (\Exception $e) {
            $this->command->error("OOPSS..an error occurred: {$e->getMessage()}");
        }
    }
}
