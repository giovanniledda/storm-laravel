<?php

namespace App\Http\Controllers\Api;

use function __;
use App\Models\ApplicationLog;
use App\Models\ApplicationLogSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApplicationLog;
use App\Http\Requests\RequestProjectChangeType;
use App\Jobs\ProjectGoogleSync;
use App\Jobs\ProjectLoadEnvironmentalData;
use App\Models\Project;
use App\Models\ReportItem;
use App\Services\AppLogEntitiesPersister;
use App\Services\ZonesPersister;
use App\Models\Task;
use App\Utils\Utils;
use App\Models\Zone;
use function array_key_exists;
use function explode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function in_array;
use function json_decode;
use function md5;
use const MEASUREMENT_FILE_TYPE;
use Net7\DocsGenerator\DocsGenerator;
use Net7\Documents\Document;
use const PROJECT_STATUS_CLOSED;
use const PROJECT_STATUSES;
use const REPORT_ENVIRONMENTAL_SUBTYPE;
use const REPORT_ITEM_TYPE_CORR_MAP_DOC;
use const REPORT_ITEM_TYPE_ENVIRONM_DOC;
use function response;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use function trim;

class ApplicationLogController extends Controller
{
    /**
     * #AL02  api/v1/application-logs/{id}/close-remarks
     *
     * @param Request $request
     * @param ApplicationLog $app_log
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function closeRemarks(Request $request, ApplicationLog $app_log)
    {
        try {
            if ($remarks_ids = $request->input('remarks_ids')) {
                $tasks = Task::whereIn('id', explode(',', $remarks_ids))->get();
                if ($tasks->count()) {
                    /** @var Task $task */
                    foreach ($tasks as $task) {
                        $task->closeMe($app_log);
                    }
                }
            }

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error closing remarks', $e->getMessage());
        }
    }

    /**
     * #AL03  api/v1/application-logs/{id}/other-remarks
     *
     * @param Request $request
     * @param ApplicationLog $app_log
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function otherRemarks(Request $request, ApplicationLog $app_log)
    {
        try {
            $remarks = $app_log->getExternallyOpenedRemarksRelatedToMyZones();

            return Utils::renderStandardJsonapiResponse(['data' => $remarks], 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error retrieving remarks', $e->getMessage());
        }
    }

    /**
     * #AL04 api/v1/application-logs/{id}/zones
     *
     * @param Request $request
     * @param ApplicationLog $app_log
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getZones(Request $request, ApplicationLog $app_log)
    {
        try {
            $zones = $app_log->getUsedZones();

            return Utils::renderStandardJsonapiResponse(['data' => $zones], 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error retrieving zones', $e->getMessage());
        }
    }
}
