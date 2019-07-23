<?php

namespace Tests\Feature;

use App\Boat;
use App\Section;
use App\Subsection;
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

        $project->tasks()->save($task)->save();

        $this->assertDatabaseHas('projects', ['name' => $project_name]);
        $this->assertDatabaseHas('tasks', ['project_id' => $project->id, 'title' => $task_title]);

        $this->assertEquals($task->title, $task_title);

        $this->assertEquals($task->project()->first()->id, $project->id);
        $this->assertEquals($task->project()->first()->name, $project->name);
    }


    function test_can_create_tasks_related_to_subsections_and_sections()
    {
        $boat = factory(Boat::class)->create();
        $this->assertInstanceOf(Boat::class, $boat);

        $sections = factory(Section::class, $this->faker->randomDigitNotNull)->create();
        $boat->sections()->saveMany($sections);

        foreach ($sections as $section) {

            $this->assertInstanceOf(Section::class, $section);

            $subsections = factory(Subsection::class, $this->faker->randomDigitNotNull)->create();
            $section->subsections()->saveMany($subsections);

            foreach ($subsections as $subsection) {

                $this->assertInstanceOf(Subsection::class, $subsection);

                $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();
                $subsection->tasks()->saveMany($tasks);
            }
        }

        foreach ($sections as $section) {
            $section_tasks_num = $section->tasks()->count();
            $subsection_tasks_num = 0;
            foreach ($section->subsections as $subsection) {
                $subsection_tasks_num +=  $subsection->tasks()->count();
            }
            $this->assertEquals($section_tasks_num, $subsection_tasks_num);
            $this->assertNotEquals($subsection_tasks_num, 0);
        }

    }

}
