<?php

namespace App\Http\Controllers\Api;

use App\ApplicationLog;
use App\ApplicationLogSection;
use App\Http\Requests\RequestApplicationLog;
use App\Jobs\ProjectLoadEnvironmentalData;
use App\ReportItem;
use App\Services\AppLogEntitiesPersister;
use App\Services\ZonesPersister;
use App\Task;
use App\Zone;
use function __;
use function array_key_exists;
use function explode;
use function in_array;
use function json_decode;
use function md5;
use function response;
use function trim;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Net7\Documents\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestProjectChangeType;
use App\Project;
use App\Utils\Utils;
use App\Jobs\ProjectGoogleSync;
use Net7\DocsGenerator\DocsGenerator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use const PROJECT_STATUSES;
use const REPORT_ENVIRONMENTAL_SUBTYPE;
use const MEASUREMENT_FILE_TYPE;
use const PROJECT_STATUS_CLOSED;
use const REPORT_ITEM_TYPE_CORR_MAP_DOC;
use const REPORT_ITEM_TYPE_ENVIRONM_DOC;

class ApplicationLogController extends Controller
{

    /**
     * #AL02  api/v1/application-logs/{id}/close-tasks
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function closeTasks(Request $request, $record)
    {
        try {
            if ($task_ids = $request->input('task_ids')) {
                $tasks = Task::whereIn('id', explode(',', $task_ids))->get();
                if ($tasks->count()) {
                    /** @var Task $task */
                    foreach ($tasks as $task) {
                        $task->closeMe($record);
                    }
                }
            }
            return Utils::renderStandardJsonapiResponse([], 204);

        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), "Error creating zones", $e->getMessage());
        }
    }

}
