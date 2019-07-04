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
                'title' => $fake_name
            ]
        );
        $task->save();
        $this->assertDatabaseHas('tasks', ['title' => $fake_name]);
    }

    function test_can_create_project_related_to_task()
    {
        $task_title = $this->faker->sentence;
        $task = new Task([
                'title' => $task_title
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
    }


    function test_can_create_storm_project_related_to_storm_task()
    {

        $storm_task_title = $this->faker->sentence;
        $task = new StormTask([
                'title' => $storm_task_title
            ]
        );
        $task->save();

        $storm_project_name = $this->faker->sentence;
        $project = new StormProject([
                'name' => $storm_project_name
            ]
        );
        $project->save();

//        $project->site()->save($site);

        $this->assertDatabaseHas('storm_projects', ['name' => $storm_project_name]);
//        $this->assertDatabaseHas('storm_tasks', ['title' => $storm_task_title]);
        $this->assertEquals($task->title, $storm_task_title);

//        $related_site = $project->site();
//        $this->assertInstanceOf(Site::class, $site);
    }

}