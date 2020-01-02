<?php

namespace Tests\Feature;

use App\Boat;
use App\Project;
use function factory;
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
}
