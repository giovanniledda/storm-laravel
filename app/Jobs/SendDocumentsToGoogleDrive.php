<?php

namespace App\Jobs;

use Exception;
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
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

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
        $this->queue = QUEUE_GDRIVE_SEND_DOCS;
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

    /**
     * The job failed to process.
     *
     * @param  Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Artisan::call('comando che rimette in coda questi job con php artisan queue:retry all --queue='gdrive-jobs' e riavvia docker');
    }
}
