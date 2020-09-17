<?php

namespace App\Jobs;

use App\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Net7\Documents\Document;

class SendDocumentsToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $project;
    private $document;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     * @param Document $document
     */
    public function __construct(Project $project, Document $document)
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
