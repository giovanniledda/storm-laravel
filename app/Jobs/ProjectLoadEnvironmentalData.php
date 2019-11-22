<?php

namespace App\Jobs;

use App\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Net7\Documents\Document;
use Net7\EnvironmentalMeasurement\Utils;
use const MEASUREMENT_FILE_TYPE;

class ProjectLoadEnvironmentalData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $project;
    private $document;

    /**
     * Create a new job instance.
     *
     * @return void
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
     * @throws \Throwable
     */
    public function handle()
    {
        $project = $this->project;
        $file_path = $project->getDocumentMediaFilePath(MEASUREMENT_FILE_TYPE);
        $array = Utils::convertCsvInAssociativeArray($file_path);
        $project->translateMeasurementsInputForTempDPHumSensor($array, 'STORM - Web App Frontend', $this->document);
    }
}
