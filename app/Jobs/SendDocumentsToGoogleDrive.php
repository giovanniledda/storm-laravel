<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDocumentsToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $project;
    private $document;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Project $project, \Net7\Documents\Document $document)
    {
        $this->project = $project;
        $this->document = $document;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->project->sendDocumentToGoogleDrive($this->document);
    }
}
