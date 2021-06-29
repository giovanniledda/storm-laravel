<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Project;
use App\Models\Section;
use App\Models\Subsection;
use App\Models\Task;
use App\Models\Zone;
use function factory;
use Tests\TestCase;

class ModelTaskTest extends TestCase
{
    public function test_can_create_task_without_project()
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

    public function test_can_create_task_related_to_project()
    {
        $task = Task::factory()->create();
        $project = Project::factory()->create();

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
    public function test_can_create_tasks_related_to_subsections_and_sections()
    {
        $boat = Boat::factory()->create();
        $this->assertInstanceOf(Boat::class, $boat);

        $sections = Section::factory()->count($this->faker->randomDigitNotNull)->create();
        $boat->sections()->saveMany($sections);

        foreach ($sections as $section) {
            $this->assertInstanceOf(Section::class, $section);

            $subsections = Subsection::factory()->count($this->faker->randomDigitNotNull)->create();
            $section->subsections()->saveMany($subsections);

            foreach ($subsections as $subsection) {
                $this->assertInstanceOf(Subsection::class, $subsection);

                $tasks = Task::factory()->count($this->faker->randomDigitNotNull)->create();
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

    public function test_internal_progressive_number()
    {
        $boats = Boat::factory()->count(3)->create();
        /** @var Boat $boat */
        foreach ($boats as $boat) {
            $projs_index_for_boat = 1;
            $tasks_index_for_boat = 1;
            $projects = Project::factory()->count(4)->create([
                'boat_id' => $boat->id,
            ]);
            /** @var Project $project */
            foreach ($projects as $project) {
                $this->assertEquals($boat->id, $project->boat->id);
                $this->assertEquals($projs_index_for_boat++, $project->internal_progressive_number);

                $tasks = Task::factory()->count(10)->create([
                    'project_id' => $project->id,
                ]);
                /** @var Task $task */
                foreach ($tasks as $task) {
                    $this->assertEquals($boat->id, $task->getProjectBoat()->id);
                    $this->assertEquals($tasks_index_for_boat++, $task->internal_progressive_number);
                }
            }
        }
    }

    public function testBasicRelationships()
    {

        /** @var Task $task1 */
        $task1 = Task::factory()->create();
        /** @var Task $task2 */
        $task2 = Task::factory()->create();
        /** @var Task $task3 */
        $task3 = Task::factory()->create();

        /** zone */
        /** $table->foreign('zone_id')->references('id')->on('zones')->onDelete('set null'); */

        /** @var Zone $zone */
        $zone = Zone::factory()->create();
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

    public function test_related_sections()
    {
        $boat = Boat::factory()->create();
        $this->assertInstanceOf(Boat::class, $boat);

        // creo 3 sezioni
        $sections_a = Section::factory()->count(3)->create();
        $boat->sections()->saveMany($sections_a);

        // verifico che l'estrazione degli id funzioni
        $sections_a_ids = $sections_a->pluck('id');
        foreach ($sections_a as $section) {
            $this->assertContains($section->id, $sections_a_ids);
        }

        // assegno 5 task ad ogni sezione e raccolgo tutti gli id dei task
        $all_tasks_a_ids = [];
        foreach ($sections_a as $section) {
            $tasks_a = Task::factory()->count(5)->create();
            $section->tasks()->saveMany($tasks_a);

            foreach ($tasks_a as $task) {
                $all_tasks_a_ids[] = $task->id;
            }
        }

        $this->assertCount(15, $all_tasks_a_ids);

        // recupero le sezioni a partire dai task id raccolti
        $probably_sections_a = Section::getSectionsStartingFromTasks($all_tasks_a_ids);
        $this->assertNotEmpty($probably_sections_a);

        /** @var Section $section */
        foreach ($probably_sections_a as $section) {
            $this->assertContains($section->id, $sections_a_ids);

            // testo il filtraggio dei task "propri"
            $my_tasks = $section->getOnlyMyTasks($all_tasks_a_ids);
            $this->assertCount(5, $my_tasks);
        }
    }
}
