<?php
/**
 * This is the list of all of the events, eloquent model fired that we can hook into

retrieved
creating
created
updating
updated
saving
saved
deleting
deleted
restoring
restored
 *
 * The retrieved event will fire when an existing model is retrieved from the database.
 * When a new model is saved for the first time, the creating and created events will fire.
 * If a model already existed in the database and the save method is called, the updating / updated events will fire.
 * However, in both cases, the saving / saved events will fire.
 */
namespace App\Observers;

use App\Jobs\ProjectGoogleDirSetup;
use App\Project;
use App\ProjectUser;
use App\Task;
use Log;

class ProjectObserver
{


    /**
     * Handle the project "updating" event.
     *
     * @param  \App\Project  $project
     * @return void
     */
   public function updating(Project $project)
    {
        $original = $project->getOriginal();

        // è cambiato lo stato del progetto
        if (isset($original['project_status']) && isset($project->project_status) && $original['project_status']!=$project->project_status) {
           Project::find($project->id)
                            ->history()
                            ->create(
                                    ['event_date'=> date("Y-m-d H:i:s", time()),
                                     'event_body'=>'project status changed to '.$project->project_status]);

        }

         // è cambiato lo stato di avanzamento
        if (isset($original['project_progress']) && isset($project->project_progress) && $original['project_progress']!=$project->project_progress) {
           Project::find($project->id)
                            ->history()
                            ->create(
                                    ['event_date'=> date("Y-m-d H:i:s", time()),
                                     'event_body'=>$project->project_progress.'% percentage']);
        }


    }

    /**
     * Handle the project "created" event.
     *
     * @param \App\Project $project
     * @return void
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function created(Project $project)
    {
        $project->setStatus(PROJECT_STATUS_IN_SITE);

        // ticket: 250, associare l'utente di sessione al progetto appena creato
        if (\Auth::check()) {
            $auth_user = \Auth::user();
            ProjectUser::createOneIfNotExists($auth_user->id, $project->id);
        }

        if (env('USE_GOOGLE_DRIVE')) {
            // uses the queue
            ProjectGoogleDirSetup::dispatch($project);
        }

        // Doc Generator from template
        $project->setupCorrosionMapTemplate();
        $project->setupEnvironmentalReportTemplate();

        // Setto l'id interno progressivo calcolato su base "per boat"
        $project->updateInternalProgressiveNumber();
    }

    /**
     * Handle the project "updated" event.
     *
     * @param \App\Project $project
     * @return void
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function updated(Project $project)
    {
        $project->setStatus($project->project_status);
    }

    /**
     * Handle the project "deleted" event.
     *
     * @param  \App\Project  $project
     * @return void
     */
    public function deleted(Project $project)
    {
        //
    }

    /**
     * Handle the project "restored" event.
     *
     * @param  \App\Project  $project
     * @return void
     */
    public function restored(Project $project)
    {
        //
    }

    /**
     * Handle the project "force deleted" event.
     *
     * @param  \App\Project  $project
     * @return void
     */
    public function forceDeleted(Project $project)
    {
        //
    }


}
