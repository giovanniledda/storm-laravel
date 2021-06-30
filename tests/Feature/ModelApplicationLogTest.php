<?php

namespace Tests\Feature;

use App\Models\ApplicationLog;
use App\Models\ApplicationLogSection;
use App\Models\ApplicationLogTask;
use App\Models\Boat;
use App\Models\Project;
use App\Models\Task;
use App\Models\Zone;
use App\Models\ZoneAnalysisInfoBlock;
//use Database\Seeders\SeederUtils;
use Seeds\SeederUtils;
use const TASK_TYPE_REMARK;
use Tests\TestCase;

class ModelApplicationLogTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {
        /** @var ApplicationLog $application_log */
        $application_log = ApplicationLog::factory()->create();

        /** @var Project $project */
        $project = Project::factory()->create();

        /** project **/
        /** $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');  **/

        // assegno le app log sections (5 elementi) all'app log
        $application_log->project()->associate($project);
        $application_log->save();

        $this->assertEquals($project->id, $application_log->project->id);

        /** @var Boat boat */
        $boat = Boat::factory()->create();
        $project->boat()->associate($boat)->save();

        $this->assertEquals($project->boat->id, $application_log->boat()->id);
    }

    public function test_internal_progressive_number()
    {
        $boats = Boat::factory()->count(3)->create();
        /** @var Boat $boat */
        foreach ($boats as $boat) {
            $projs_index_for_boat = 1;
            $application_logs_index_for_boat = 1;
            $projects = Project::factory()->count(4)->create([
                'boat_id' => $boat->id,
            ]);
            /** @var Project $project */
            foreach ($projects as $project) {
                $this->assertEquals($boat->id, $project->boat->id);
                $this->assertEquals($projs_index_for_boat++, $project->internal_progressive_number);

                $application_logs = ApplicationLog::factory()->count(10)->create([
                    'project_id' => $project->id,
                ]);
                /** @var ApplicationLog $application_log */
                foreach ($application_logs as $application_log) {
                    $this->assertEquals($boat->id, $application_log->boat()->id);
                    $this->assertEquals($application_logs_index_for_boat++, $application_log->internal_progressive_number);
                }
            }
        }
    }

    public function test_started_sections()
    {
        $application_log_sections_num = $this->faker->numberBetween(1, 15);
        $application_log_sections_started_num = $started_counter = $this->faker->numberBetween(1, $application_log_sections_num);

        /** @var ApplicationLog $application_log */
        $application_log = ApplicationLog::factory()->create();
        $application_log_sections = ApplicationLogSection::factory()->count($application_log_sections_num)->create();

        /** application_log **/
        /** $table->foreign('application_log_id')->references('id')->on('application_logs')->onDelete('set null'); **/

        // assegno le app log sections (5 elementi) all'app log
        $application_log->application_log_sections()->saveMany($application_log_sections);

        $this->assertEquals($application_log_sections_num, $application_log->application_log_sections()->count());

        foreach ($application_log_sections as $application_log_section) {
            $this->assertEquals($application_log_section->application_log_id, $application_log->id);
            $this->assertEquals($application_log_section->application_log->id, $application_log->id); // testo la relazione inversa

            if ($started_counter > 0) {
                $started_counter--;
                $application_log_section->update([
                    'is_started' => 1,
                ]);
            } else {
                $application_log_section->update([
                    'is_started' => 0,
                ]);
            }
        }

        $this->assertEquals($application_log_sections_started_num, $application_log->countStartedSections());
    }

    /**
     * I Task (di tipo "remark") sono ora associati agli App Log.
     * Possono avere un App Log di apertura e uno di chiusura.
     */
    public function test_open_and_close_tasks()
    {

        /** @var ApplicationLog $application_log */
        $application_log = ApplicationLog::factory()->create();

        /** @var Task $task */
        $task = Task::factory()->create();

        /**
         *  ------------ OPENING ------------
         */

        /** @var ApplicationLogTask $app_log_task */
        $app_log_task = ApplicationLogTask::factory()->create(
            [
                'task_id' => $task->id,
                'application_log_id' => $application_log->id,
                'action' => 'open',
            ]
        );

        $this->assertEquals($task->opener_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->opened_tasks()->first()->id, $task->id);

        // on delete cascade
        $task->delete();
        $this->assertEquals($application_log->opened_tasks()->count(), 0);

        // try with attach/detach
        /** @var Task $task2 */
        $task2 = Task::factory()->create();

        $application_log->opened_tasks()->attach($task2->id, ['action' => 'open']);

        $this->assertEquals($task2->opener_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->opened_tasks()->first()->id, $task2->id);

        $application_log->opened_tasks()->detach($task2->id);
        $this->assertEquals($application_log->opened_tasks()->count(), 0);

        /**
         *  ------------ CLOSING ------------
         */

        /** @var ApplicationLogTask $app_log_task */
        $app_log_task2 = ApplicationLogTask::factory()->create(
            [
                'task_id' => $task2->id,
                'application_log_id' => $application_log->id,
                'action' => 'close',
            ]
        );

        $this->assertEquals($task2->closer_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->closed_tasks()->first()->id, $task2->id);

        // on delete cascade
        $task2->delete();
        $this->assertEquals($application_log->opened_tasks()->count(), 0);

        // try with attach/detach, opening and closing the same new task3
        /** @var Task $task3 */
        $task3 = Task::factory()->create();

        $application_log->opened_tasks()->attach($task3->id, ['action' => 'open']);
        $this->assertEquals($task3->opener_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->opened_tasks()->first()->id, $task3->id);

        $application_log->closed_tasks()->attach($task3->id, ['action' => 'close']);
        $this->assertEquals($task3->closer_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->closed_tasks()->first()->id, $task3->id);

        // try with new functions
        /** @var Task $task4 */
        $task4 = Task::factory()->create();

        // ... openTask
        $application_log->openTask($task4);
        $this->assertEquals($task4->opener_application_log()->first()->id, $application_log->id);

        // ... closeTask
        $application_log->closeTask($task4);
        $this->assertEquals($task4->closer_application_log()->first()->id, $application_log->id);

        /** @var Task $task5 */
        $task5 = Task::factory()->create([
            'project_id' => Project::factory()->create()->id,
        ]);

        // .. openMe
        $task5->openMe($application_log);
        $this->assertEquals($task5->opener_application_log()->first()->id, $application_log->id);

        // .. closeMe
        $task5->closeMe($application_log);
        $this->assertEquals($task5->closer_application_log()->first()->id, $application_log->id);
    }

    // test for getOpenedTaskFromMyZones function
    public function test_opened_tasks_from_my_zones()
    {
        $utils = new \Database\Seeders\SeederUtils();

        /** @var Project $project */
        $project = Project::factory()->create();

        // adding Zones to project
        $utils->addFakeZonesToProject($project, 2, 4);

        /** @var ApplicationLog $first_application_log */
        $first_application_log = ApplicationLog::factory()->create(
            [
                'project_id' => $project->id,
            ]
        );

        // Adding "ZONES" section to app log
        /** @var ApplicationLogSection $section_zone */
        $section_zone = $utils->buildZonesApplicationLogSection($first_application_log, $project, [], true);  // this creates 2 zones_ib related to 2 random zones of the project (*)
        $first_application_log->application_log_sections()->save($section_zone);

        // extract the choosen zones (*)
        $zones = [];
        $zone_ib = $section_zone->zone_analysis_info_blocks;
        /** @var ZoneAnalysisInfoBlock $item */
        foreach ($zone_ib as $item) {
            $zones[] = $item->zone;
        }

        // Creating some remarks (Task) who bind to the first of those zones
        /** @var Task $task1 */
        $task1 = Task::factory()->create(
            [
                'project_id' => $project->id,
                'is_open' => true,
                'task_type' => TASK_TYPE_REMARK,
            ]
        );
        /** @var Task $task2 */
        $task2 = Task::factory()->create(
            [
                'project_id' => $project->id,
                'is_open' => true,
                'task_type' => TASK_TYPE_REMARK,
            ]
        );
        /** @var Task $task3 */
        $task3 = Task::factory()->create(
            [
                'project_id' => $project->id,
                'is_open' => true,
                'task_type' => TASK_TYPE_REMARK,
            ]
        );

        /** @var Zone $zone1 */
        $zone1 = $this->faker->randomElement($zones);

        // ...associating remarks to zone1
        $zone1->tasks()->save($task1);
        $zone1->tasks()->save($task2);
        $zone1->tasks()->save($task3);

        // Creating some other remarks (Task) who bind to the other zone
        /** @var Task $task4 */
        $task4 = Task::factory()->create(
            [
                'project_id' => $project->id,
                'is_open' => true,
                'task_type' => TASK_TYPE_REMARK,
            ]
        );
        /** @var Task $task5 */
        $task5 = Task::factory()->create(
            [
                'project_id' => $project->id,
                'is_open' => true,
                'task_type' => TASK_TYPE_REMARK,
            ]
        );
        /** @var Task $task6 */
        $task6 = Task::factory()->create(
            [
                'project_id' => $project->id,
                'is_open' => true,
                'task_type' => TASK_TYPE_REMARK,
            ]
        );

        do {
            /** @var Zone $zone2 */
            $zone2 = $this->faker->randomElement($zones);
        } while ($zone2->id != $zone1->id);

        // ...associating remarks to zone2
        $zone2->tasks()->save($task4);
        $zone2->tasks()->save($task5);
        $zone2->tasks()->save($task6);

        // open some tasks from the initial App Log
        $task1->openMe($first_application_log);
        $this->assertEquals($task1->opener_application_log()->first()->id, $first_application_log->id);

        $task2->openMe($first_application_log);
        $this->assertEquals($task2->opener_application_log()->first()->id, $first_application_log->id);

//        $task3->openMe($application_log);

        $task4->openMe($first_application_log);
        $this->assertEquals($task4->opener_application_log()->first()->id, $first_application_log->id);

        $task5->openMe($first_application_log);
        $this->assertEquals($task5->opener_application_log()->first()->id, $first_application_log->id);

//        $task6->openMe($application_log);

        // Now I've to create a different App Log wich uses the same zones in its zone_ib
        /** @var ApplicationLog $second_application_log */
        $second_application_log = ApplicationLog::factory()->create();

        // Adding "ZONES" section to app log
        /** @var ApplicationLogSection $section_zone */
        $section_zone2 = $utils->buildZonesApplicationLogSection($second_application_log, $project, $zones, true);  // this creates 2 zones_ib related to $zones parameter
        $second_application_log->application_log_sections()->save($section_zone2); // ...now $first_application_log and $other_application_log have $zones in common

        // opening the remaining two remark from $other_application_log
        $task3->openMe($second_application_log);
        $this->assertEquals($task3->opener_application_log()->first()->id, $second_application_log->id);

        $task6->openMe($second_application_log);
        $this->assertEquals($task6->opener_application_log()->first()->id, $second_application_log->id);

        // finally get "other" tasks for both
        $first_app_log_tasks_collection = $first_application_log->getExternallyOpenedRemarksRelatedToMyZones();
        $second_app_log_tasks_collection = $second_application_log->getExternallyOpenedRemarksRelatedToMyZones();

        $this->assertNotEmpty($first_app_log_tasks_collection);
        $this->assertNotEmpty($second_app_log_tasks_collection);

        // check for 1st app log
        $first_application_log_tasks = [$task1->id, $task2->id, $task4->id, $task5->id];
        $other_application_log_tasks = [$task3->id, $task6->id];

        foreach ($first_app_log_tasks_collection as $t) {
            $this->assertNotContains($t->id, $first_application_log_tasks);
            $this->assertContains($t->id, $other_application_log_tasks);
        }

        // check for 2nd app log
        foreach ($second_app_log_tasks_collection as $t) {
            $this->assertContains($t->id, $first_application_log_tasks);
            $this->assertNotContains($t->id, $other_application_log_tasks);
        }
    }
}
