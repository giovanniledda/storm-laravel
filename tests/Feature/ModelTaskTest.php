<?php

namespace Tests\Feature;

use App\Task;
use App\Project;
use Tests\TestCase;

class ModelTaskTest extends TestCase
{
    function test_can_create_task_without_project()
    {
        $fake_name = $this->faker->sentence;
        $task = new Task([
                'title' => $fake_name,
                'description' => $this->faker->text,
            ]
        );
        $task->save();
        $this->assertDatabaseHas('tasks', ['title' => $fake_name]);
    }

    function test_can_create_task_related_to_project()
    {
        $task_title = $this->faker->sentence;
        $task = new Task([
                'title' => $task_title,
                'description' => $this->faker->text,
            ]
        );
        $task->save();

        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name
            ]
        );
        $project->save();

        $project->tasks()->save($task);

        $this->assertDatabaseHas('projects', ['name' => $project_name]);
        $this->assertDatabaseHas('tasks', ['project_id' => $project->id, 'title' => $task_title]);

        $this->assertEquals($task->title, $task_title);
        $this->assertEquals($task->project->id, $project->id);  // per poter chiamare $task->project devo aver messo "project" tra gli $ownAttributes, altrimenti devo chiamarlo con $task->project()->first()
        $this->assertEquals($task->project->name, $project->name);
    }


}
