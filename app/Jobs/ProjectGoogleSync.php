<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProjectGoogleSync implements ShouldQueue
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
        $this->project->checkForUpdatedFilesOnGoogleDrive(); //->onQueue($this->project->getGoogleSyncQueueName());
    }
}
