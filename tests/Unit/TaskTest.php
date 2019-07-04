<?php

namespace Tests\Unit;

use App\Task;
use App\Project;
use Tests\TestCase;
use App\Storm\StormProject;
use App\Storm\StormTask;

class TaskTest extends TestCase
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

    function test_can_create_project_related_to_task()
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


    function test_can_create_storm_project_related_to_storm_task()
    {

        $storm_task_title = $this->faker->sentence;
        $storm_task_desc = $this->faker->text;

        $s_task = Task::create(StormTask::class, [
            'title' => $storm_task_title,
            'description' => $storm_task_desc,
            'operation_type' => 'idraulic',
        ]);

        $storm_project_name = $this->faker->sentence;

        $s_project = Project::create(StormProject::class, [
            'name' => $storm_project_name,
            'type' => 'refit',
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
        ]);

        $this->assertEquals($s_task->title, $storm_task_title);  // parent attribute
        $this->assertEquals($s_task->description, $storm_task_desc);  // parent attribute
        $this->assertEquals($s_task->operation_type, 'idraulic');  // child attribute
        $this->assertEquals($s_task->entity->saySomething(), 'Something');  // child method

        $this->assertEquals($s_project->name, $storm_project_name);   // parent attribute
        $this->assertEquals($s_project->type, 'refit');   // child attribute

        $s_project->tasks()->save($s_task);  // parent method
        $this->assertEquals($s_project->tasks()->first()->title, $s_task->title);  // parent attribute
        $this->assertEquals($s_project->tasks()->first()->description, $s_task->description);  // parent attribute
        $this->assertEquals($s_project->tasks()->first()->operation_type, $s_task->operation_type);  // child attribute
        $this->assertEquals($s_project->tasks()->first()->entity->saySomething(), $s_task->entity->saySomething());  // child method

        $this->assertEquals($s_task->project->name, $s_project->name); // parent attribute
        $this->assertEquals($s_task->project->type, $s_project->type); // child attribute

        // Querying Relations
    }

}