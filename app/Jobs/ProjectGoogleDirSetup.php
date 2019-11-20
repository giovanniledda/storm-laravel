<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProjectGoogleDirSetup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $project;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $projectDocumentsPath = $this->project->getGoogleProjectDocumentsFolderPath();
        // this will create the directory in the google drive account
        $this->project->getGooglePathFromHumanPath($projectDocumentsPath);

        $projectReportsPath = $this->project->getGoogleProjectReportsFolderPath();
        // this will create the directory in the google drive account
        $this->project->getGooglePathFromHumanPath($projectReportsPath);


    }
}
