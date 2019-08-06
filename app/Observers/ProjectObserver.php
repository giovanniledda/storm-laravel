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

use App\Project;
use function file_put_contents;
use const PROJECT_STATUS_OPEN;
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
        
        // è cambiato il tipo del progetto
        if (isset($original['project_type']) && isset($project->project_type) && $original['project_type']!=$project->project_type) {
           
        }
        
         // è cambiato lo stato di avanzamento
        if (isset($original['project_progress']) && isset($project->project_progress) && $original['project_progress']!=$project->project_progress) {
           
        }
         
        
    }
    
    
    /**
     * Handle the project "created" event.
     *
     * @param  \App\Project  $project
     * @return void
     */
    public function created(Project $project)
    {
        $project->setStatus(PROJECT_STATUS_OPEN); 
    }

    /**
     * Handle the project "updated" event.
     *
     * @param  \App\Project  $project
     * @return void
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
