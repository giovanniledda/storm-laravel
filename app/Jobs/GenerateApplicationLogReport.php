<?php

namespace App\Jobs;

use App\ApplicationLog;
use App\Project;
use App\ReportItem;
use App\Services\ReportGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use const REPORT_APPLOG_SUBTYPE;
use const REPORT_ITEM_TYPE_CORR_MAP_DOC;

class GenerateApplicationLogReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId;
    protected $applicationLogId;
    protected $template;
    protected $subtype;
    protected $userId;
    protected $reportItemId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    public function __construct(
        $projectId,
        $applicationLogId,
        $template,
        $subtype,
        $userId,
        $reportItemId
    ) {
        $this->projectId = $projectId;
        $this->applicationLogId = $applicationLogId;
        $this->template = $template;
        $this->subtype = $subtype;
        $this->userId = $userId;
        $this->reportItemId = $reportItemId;
    }

    public function handle(): bool
    {

        /** @var Project $project */
        $project = Project::findOrFail($this->projectId);

        /** @var ApplicationLog $applicationLog */
        $applicationLog = ApplicationLog::findOrFail($this->applicationLogId);
        $project->setCurrentAppLog($applicationLog);
        $project->setTasksToIncludeInReport($applicationLog->opened_tasks()->pluck('id')->toArray());

        $document = ReportGenerator::reportGenerationProcess($this->template, $project, $this->subtype);

        if ($document) {
            /** @var ReportItem $reportItem */
            $reportItem = ReportItem::findOrFail($this->reportItemId);
            $reportItem->updateForDocument(
                $document,
                REPORT_APPLOG_SUBTYPE,
                $this->userId,
                $project->id,
                [
                    'id' => $document->id,
                    'app_log_type' => $project->getCurrentAppLogType(),
                    'app_log_name' => $applicationLog->name,
                    'zones' => $project->getCurrentAppLogZones(),
                ]
            );
        }
        $project->closeAllTasksTemporaryFiles();

        return true;
    }
}
