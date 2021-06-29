<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use const MEASUREMENT_FILE_TYPE;
use Net7\Documents\Document;
use Net7\EnvironmentalMeasurement\Utils;

class ProjectLoadEnvironmentalData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $project;
    private $document;
    private $data_source;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     * @param Document $document
     * @param string $data_source
     */
    public function __construct(Project $project, Document $document, string $data_source = null)
    {
        $this->project = $project;
        $this->document = $document;
        $this->data_source = $data_source;
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
        $project->translateMeasurementsInputForTempDPHumSensor($array, $this->data_source ? $this->data_source : MEASUREMENT_DEFAULT_DATA_SOURCE, $this->document);
    }
}
