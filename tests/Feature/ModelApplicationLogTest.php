<?php

namespace Tests\Feature;

use App\ApplicationLogSection;
use App\ApplicationLogTask;
use App\Task;
use function factory;
use App\Boat;
use App\Project;
use Tests\TestCase;
use App\ApplicationLog;

class ModelApplicationLogTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {
        /** @var ApplicationLog $application_log */
        $application_log = factory(ApplicationLog::class)->create();

        /** @var Project $project */
        $project = factory(Project::class)->create();

        /** project **/
        /** $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');  **/

        // assegno le app log sections (5 elementi) all'app log
        $application_log->project()->associate($project);
        $application_log->save();

        $this->assertEquals($project->id, $application_log->project->id);

        /** @var Boat boat */
        $boat = factory(Boat::class)->create();
        $project->boat()->associate($boat)->save();

        $this->assertEquals($project->boat->id, $application_log->boat()->id);
    }

    function test_internal_progressive_number() {

        $boats = factory(Boat::class, 3)->create();
        /** @var Boat $boat */
        foreach ($boats as $boat) {
            $projs_index_for_boat = 1;
            $application_logs_index_for_boat = 1;
            $projects = factory(Project::class, 4)->create([
                'boat_id' => $boat->id
            ]);
            /** @var Project $project */
            foreach ($projects as $project) {
                $this->assertEquals($boat->id, $project->boat->id);
                $this->assertEquals($projs_index_for_boat++, $project->internal_progressive_number);

                $application_logs = factory(ApplicationLog::class, 10)->create([
                    'project_id' => $project->id
                ]);
                /** @var ApplicationLog $application_log */
                foreach ($application_logs as $application_log) {
                    $this->assertEquals($boat->id, $application_log->boat()->id);
                    $this->assertEquals($application_logs_index_for_boat++, $application_log->internal_progressive_number);
                }
            }
        }
    }

    function test_started_sections() {

        $application_log_sections_num = $this->faker->numberBetween(1, 15);
        $application_log_sections_started_num = $started_counter = $this->faker->numberBetween(1, $application_log_sections_num);

        /** @var ApplicationLog $application_log */
        $application_log = factory(ApplicationLog::class)->create();
        $application_log_sections = factory(ApplicationLogSection::class, $application_log_sections_num)->create();

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
                    'is_started' => 1
                ]);
            } else {
                $application_log_section->update([
                    'is_started' => 0
                ]);
            }
        }

        $this->assertEquals($application_log_sections_started_num, $application_log->countStartedSections());
    }

    /**
     * I Task (di tipo "remark") sono ora associati agli App Log.
     * Possono avere un App Log di apertura e uno di chiusura.
     */
    function test_open_and_close_tasks() {

        /** @var ApplicationLog $application_log */
        $application_log = factory(ApplicationLog::class)->create();

        /** @var Task $task */
        $task = factory(Task::class)->create();

        /**
         *  ------------ OPENING ------------
         */

        /** @var ApplicationLogTask $app_log_task */
        $app_log_task = factory(ApplicationLogTask::class)->create(
            [
                'task_id' => $task->id,
                'application_log_id' => $application_log->id,
                'action' => 'open'
            ]
        );

        $this->assertEquals($task->opener_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->opened_tasks()->first()->id, $task->id);

        // on delete cascade
        $task->delete();
        $this->assertEquals($application_log->opened_tasks()->count(), 0);

        // try with attach/detach
        /** @var Task $task2 */
        $task2 = factory(Task::class)->create();

        $application_log->opened_tasks()->attach($task2->id, ['action' => 'open']);

        $this->assertEquals($task2->opener_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->opened_tasks()->first()->id, $task2->id);

        $application_log->opened_tasks()->detach($task2->id);
        $this->assertEquals($application_log->opened_tasks()->count(), 0);

        /**
         *  ------------ CLOSING ------------
         */

        /** @var ApplicationLogTask $app_log_task */
        $app_log_task2 = factory(ApplicationLogTask::class)->create(
            [
                'task_id' => $task2->id,
                'application_log_id' => $application_log->id,
                'action' => 'close'
            ]
        );

        $this->assertEquals($task2->closer_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->closed_tasks()->first()->id, $task2->id);

        // on delete cascade
        $task2->delete();
        $this->assertEquals($application_log->opened_tasks()->count(), 0);

        // try with attach/detach, opening and closing the same new task3
        /** @var Task $task3 */
        $task3 = factory(Task::class)->create();

        $application_log->opened_tasks()->attach($task3->id, ['action' => 'open']);
        $this->assertEquals($task3->opener_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->opened_tasks()->first()->id, $task3->id);

        $application_log->closed_tasks()->attach($task3->id, ['action' => 'close']);
        $this->assertEquals($task3->closer_application_log()->first()->id, $application_log->id);
        $this->assertEquals($application_log->closed_tasks()->first()->id, $task3->id);

        // try with new functions
        /** @var Task $task4 */
        $task4 = factory(Task::class)->create();

        $application_log->openTask($task4);
        $this->assertEquals($task4->opener_application_log()->first()->id, $application_log->id);

        $application_log->closeTask($task4);
        $this->assertEquals($task4->closer_application_log()->first()->id, $application_log->id);
    }
}
