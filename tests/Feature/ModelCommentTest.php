<?php

namespace Tests\Feature;

use App\Boat;
use App\Comment;
use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Permission;
use App\Project;
use App\Role;
use App\Task;
use Tests\TestCase;
use App\User;

use const PERMISSION_BOAT_MANAGER;
use const ROLE_BOAT_MANAGER;

class ModelCommentTest extends TestCase
{
    function test_can_create_comment_related_to_task()
    {
        // Creo barca
        $boat = factory(Boat::class)->create();

        // Creo progetto e lo associo alla barca
        $project = factory(Project::class)->create();
        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->id, $project->boat->id);

        // Creo utenti da assegnare al progetto
        $users = factory(User::class, $this->faker->randomDigitNotNull)->create();
        $this->assertNotCount(0, $users);

        foreach ($users as $user) {
            // ruoli e permessi ad utente

            // associo utente al progetto
            $user->projects()->attach($project->id, ['role' => PROJECT_USER_ROLE_OWNER]);
            $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $user->id]);
        }

        // Creo i task e li assegno al progetto
        $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();
//        $project->tasks()->saveMany($tasks);  // Vedi mail di Ledda del 24 luglio: se uso questa poi $t->project è null :-(

        foreach ($tasks as $task) {
            $this->assertInstanceOf(Task::class, $task);

            $task->project()->associate($project)->save();
            $this->assertNotNull($task->project->id);
            $this->assertEquals($task->project_id, $project->id);

            // associo i commenti agli autori
            foreach ($users as $user) {
                $comments = factory(Comment::class, $this->faker->randomDigitNotNull)->create();

                foreach ($comments as $comment) {
                    $comment->author()->associate($user)->save();
                }

                // ...e al task
                $task->comments()->saveMany($comments);
                $this->assertDatabaseHas('comments', ['commentable_type' => Task::class]);
            }
        }


    }
}
