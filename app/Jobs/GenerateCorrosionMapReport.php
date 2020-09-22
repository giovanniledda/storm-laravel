<?php

namespace App\Jobs;

use App\Project;
use App\ReportItem;
use App\Services\ReportGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

use const REPORT_ITEM_TYPE_CORR_MAP_DOC;

class GenerateCorrosionMapReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId;
    protected $tasksToIncludeInReport;
    protected $selectOnlyPublicTasks;
    protected $template;
    protected $subtype;
    protected $userId;

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
    public $retryAfter = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    public function __construct(
        $projectId,
        $tasksToIncludeInReport,
        $selectOnlyPublicTasks,
        $template,
        $subtype,
        $userId
    ) {
        $this->projectId = $projectId;
        $this->tasksToIncludeInReport = $tasksToIncludeInReport;
        $this->selectOnlyPublicTasks = $selectOnlyPublicTasks;
        $this->template = $template;
        $this->subtype = $subtype;
        $this->userId = $userId;
    }

    public function handle(): bool
    {
        $project = Project::findOrFail($this->projectId);
        $project->setTasksToIncludeInReport($this->tasksToIncludeInReport, $this->selectOnlyPublicTasks);
        $reportGenerator = new ReportGenerator();
        $document = $reportGenerator->reportGenerationProcess($this->template, $project, $this->subtype);

        if ($document) {
            ReportItem::touchForNewDocument(
                $document,
                REPORT_ITEM_TYPE_CORR_MAP_DOC,
                $this->userId,
                $project->id,
                [
                    'id' => $document->id,
                ]
            );
        }

        $project->closeAllTasksTemporaryFiles();
        return true;
    }
}
