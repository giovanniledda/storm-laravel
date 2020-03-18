<?php

namespace Tests\Feature;

use App\Boat;
use App\Section;
use App\Subsection;
use App\Task;
use App\Project;
use App\Zone;
use Tests\TestCase;
use function factory;

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
        $task = factory(Task::class)->create();
        $project = factory(Project::class)->create();

//        $project->tasks()->save($task)->save(); NOTA: se faccio questo, poi non posso fare $task->project ..mi dice che è nullo
        $task->project()->associate($project)->save();

        $this->assertDatabaseHas('projects', ['name' => $project->name]);
        $this->assertDatabaseHas('tasks', ['project_id' => $project->id, 'title' => $task->title]);

        $this->assertEquals($task->project->id, $project->id);
        $this->assertEquals($task->project->name, $project->name);
    }


    // Per ora il test per com'è fallisce sempre perché cerca tra i task direttamente associati alla section
    // anche quelli delle subsection...ma non c'è nessun modo di prenderli in automatico...ndrebbe messo un metodo
    // dammiITaskDelleSubsectionEMiei  (cioe' della section) in section
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
                $subsection_tasks_num += $subsection->tasks()->count();
            }
            $this->assertEquals($section_tasks_num, $subsection_tasks_num, "TEST FALLITO? NO PROBLEM! Vedere commento a riga 42 del file.\n");
            $this->assertNotEquals($subsection_tasks_num, 0);
        }

    }

    function test_internal_progressive_number() {

        $boats = factory(Boat::class, 3)->create();
        /** @var Boat $boat */
        foreach ($boats as $boat) {
            $projs_index_for_boat = 1;
            $tasks_index_for_boat = 1;
            $projects = factory(Project::class, 4)->create([
                'boat_id' => $boat->id
            ]);
            /** @var Project $project */
            foreach ($projects as $project) {
                $this->assertEquals($boat->id, $project->boat->id);
                $this->assertEquals($projs_index_for_boat++, $project->internal_progressive_number);

                $tasks = factory(Task::class, 10)->create([
                    'project_id' => $project->id
                ]);
                /** @var Task $task */
                foreach ($tasks as $task) {
                    $this->assertEquals($boat->id, $task->getProjectBoat()->id);
                    $this->assertEquals($tasks_index_for_boat++, $task->internal_progressive_number);
                }
            }
        }
    }

    function testBasicRelationships() {

        /** @var Task $task1 */
        $task1 = factory(Task::class)->create();
        /** @var Task $task2 */
        $task2 = factory(Task::class)->create();
        /** @var Task $task3 */
        $task3 = factory(Task::class)->create();

        /** zone */
        /** $table->foreign('zone_id')->references('id')->on('zones')->onDelete('set null'); */

        /** @var Zone $zone */
        $zone = factory(Zone::class)->create();
        $task1->zone()->associate($zone);
        $task1->save();
        $task2->zone()->associate($zone);
        $task2->save();

        // dalla Zone
        $zone->tasks()->save($task3);

        $this->assertContains($task1->id, $zone->tasks()->pluck('id')); // testo la relazione inversa
        $this->assertContains($task2->id, $zone->tasks()->pluck('id')); // testo la relazione inversa
        $this->assertContains($task3->id, $zone->tasks()->pluck('id')); // testo la relazione inversa

        $this->assertEquals($zone->id, $task1->zone->id);
        $this->assertEquals($zone->id, $task2->zone->id);
        $this->assertEquals($zone->id, $task3->zone->id);

    }
}
