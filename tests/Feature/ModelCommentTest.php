<?php

namespace Tests\Feature;

use App\Boat;
use App\Comment;
use App\Notifications\TaskCreated;
use App\Notifications\TaskUpdated;
use App\Permission;
use App\Profession;
use App\Project;
use App\Role;
use App\Task;
use App\User;
use const PERMISSION_BOAT_MANAGER;
use const ROLE_BOAT_MANAGER;
use Tests\TestCase;

class ModelCommentTest extends TestCase
{
    public function test_can_create_comment_related_to_task()
    {
        $this->_populateProfessions();
        // Creo barca
        $boat = Boat::factory()->create();

        // Creo progetto e lo associo alla barca
        $project = Project::factory()->create();
        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->id, $project->boat->id);

        // Creo utenti da assegnare al progetto
        $users = User::factory()->count($this->faker->randomDigitNotNull)->create();
        $this->assertNotCount(0, $users);

        foreach ($users as $user) {
            // ruoli e permessi ad utente

            // associo utente al progetto
            $user->projects()->attach($project->id, ['profession_id' => 1]);
            $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $user->id]);
        }

        // Creo i task e li assegno al progetto
        $tasks = Task::factory()->count($this->faker->randomDigitNotNull)->create();
//        $project->tasks()->saveMany($tasks);  // Vedi mail di Ledda del 24 luglio: se uso questa poi $t->project è null :-(

        foreach ($tasks as $task) {
            $this->assertInstanceOf(Task::class, $task);

            $task->project()->associate($project)->save();
            $this->assertNotNull($task->project->id);
            $this->assertEquals($task->project_id, $project->id);

            // associo i commenti agli autori
            foreach ($users as $user) {
                $comments = Comment::factory()->count($this->faker->randomDigitNotNull)->create();

                foreach ($comments as $comment) {
                    $comment->author()->associate($user)->save();
                }

                // ...e al task
                $task->comments()->saveMany($comments);
                $this->assertDatabaseHas('comments', ['commentable_type' => Task::class, 'commentable_id' => $task->id]);
            }
        }
    }

    private function _populateProfessions()
    {
        $professions = ['owner', 'chief engineer', 'captain', 'ship\'s boy'];
        foreach ($professions as $profession) {
            $prof = Profession::create(['name'=>$profession]);
            $prof->save();
        }
    }
}
