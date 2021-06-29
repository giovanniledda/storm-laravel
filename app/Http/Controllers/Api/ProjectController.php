<?php

namespace App\Http\Controllers\Api;

use function __;
use App\ApplicationLog;
use App\ApplicationLogSection;
use App\Boat;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApplicationLog;
use App\Http\Requests\RequestProjectChangeType;
use App\Jobs\GenerateApplicationLogReport;
use App\Jobs\GenerateCorrosionMapReport;
use App\Jobs\ProjectGoogleSync;
use App\Jobs\ProjectLoadEnvironmentalData;
use App\Project;
use App\ReportItem;
use App\Services\AppLogEntitiesPersister;
use App\Services\ReportGenerator;
use App\Services\ZonesPersister;
use App\Task;
use App\Utils\Utils;
use App\Zone;
use function array_key_exists;
use function explode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use function in_array;
use function json_decode;
use function md5;
use const MEASUREMENT_FILE_TYPE;
use Net7\DocsGenerator\DocsGenerator;
use Net7\Documents\Document;
use const PERMISSION_ADMIN;
use const PERMISSION_BACKEND_MANAGER;
use const PROJECT_STATUS_CLOSED;
use const PROJECT_STATUSES;
use const REPORT_APPLOG_SUBTYPE;
use const REPORT_CORROSION_MAP_SUBTYPE;
use const REPORT_ENVIRONMENTAL_SUBTYPE;
use const REPORT_ITEM_TYPE_CORR_MAP_DOC;
use const REPORT_ITEM_TYPE_ENVIRONM_DOC;
use function response;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use function trim;

class ProjectController extends Controller
{
    /**
     * @var AppLogEntitiesPersister
     */
    protected $_app_log_persister;
    protected $_zones_persister;

    public function __construct(AppLogEntitiesPersister $al_persister, ZonesPersister $z_persister)
    {
        $this->_app_log_persister = $al_persister;
        $this->_zones_persister = $z_persister;
    }

    /**
     * Ritorna i possibili stati usati nei progetti.
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function statuses(Request $request)
    {
        return Utils::renderStandardJsonapiResponse(
            [
                'data' => [
                    'type' => 'projects',
                    'attributes' => ['statuses' => PROJECT_STATUSES],
                ],
            ],
            200
        );
    }

    /**
     * Presenta lo storico dei progetti.
     * @param Request $request
     * @param type $related
     * @return type
     */
    public function history(Request $request, $related)
    {
        $project = json_decode($related, true);
        $histories = Project::find($project['id'])->history()->get()->toArray();
        $data = [];
        foreach ($histories as $history) {
            array_push(
                $data,
                [
                    'type' => 'projects',
                    'attributes' => ['event' => $history['event_body']],
                ]
            );
        }

        return Utils::renderStandardJsonapiResponse(['data' => $data], 200);
        //  exit();
    }

    public function close(Request $request, $related)
    {
        $data = json_decode($related, true);
        // indica se chiudere il progetto mettendo i task o no.
        $force = isset($request->data['attributes']['force']) ? $request->data['attributes']['force'] : 0;
        $closeResponse = Project::findOrFail($data['id'])->close($force);

        //   Log::info("i'm here", $request);

        /**
         * @todo segnare nella history del progetto l'evento se serve.
         */
        if ($closeResponse['success']) {
            $ret = [
                'data' => [
                    'type' => 'projects',
                    'id' => $data['id'],
                    'attributes' => [
                        'success' => $closeResponse['success'],
                        'status' => PROJECT_STATUS_CLOSED,
                        'force' => $force,
                        'tasks' => $closeResponse['tasks'],
                    ],
                ],
            ];

            return Response($ret, 202);
        } else {
            $ret = [
                'data' => [
                    'type' => 'projects',
                    'id' => $data['id'],
                    'attributes' => [
                        'success' => $closeResponse['success'],
                        'status' => $data['project_status'],
                        'force' => $force,
                        'tasks' => $closeResponse['tasks'],
                    ],
                ],
            ];

            return Response($ret, 200);
        }
    }

    /**
     * API used to change the project type
     *
     * @param RequestProjectChangeType $request
     * @param $record
     *
     * @return mixed
     */
    public function changeType(RequestProjectChangeType $request, $record)
    {
        /** @var Project $project */
        $project = Project::findOrFail($record->id);
        if ($project->project_status == PROJECT_STATUS_CLOSED) {
            return Utils::jsonAbortWithInternalError(
                422,
                130,
                PROJECT_TYPE_API_VALIDATION_TITLE,
                PROJECT_TYPE_API_PROJECT_CLOSED_MSG
            );
        }
        if ($type = $request->input('data.attributes.type')) {
            if ($type != $project->project_type) {
                $newProject = $project->replicate();
                $newProject->project_type = $type;
                $newProject->project_status = PROJECT_STATUS_IN_SITE;
                $newProject->save();

                // copio gli utenti associati
                $project->transferMyUsersToProject($newProject);

                // copio le zone
                if ($request->has('data.attributes.import_zones')) {
                    $project->transferMyZonesToProject($newProject);
                }

                // archivio il vecchio
                $project->close(1);

                $ret = [
                    'data' => [
                        'type' => 'projects',
                        'id' => $newProject->id,
                        'attributes' => $newProject,
                    ],
                ];

                return Utils::renderStandardJsonapiResponse($ret, 200);
            } else {
                return Utils::jsonAbortWithInternalError(
                    422,
                    130,
                    PROJECT_TYPE_API_VALIDATION_TITLE,
                    PROJECT_TYPE_API_NO_ACTION_MSG
                );
            }
        }

        return Utils::jsonAbortWithInternalError(
            422,
            130,
            PROJECT_TYPE_API_VALIDATION_TITLE,
            PROJECT_TYPE_API_VALIDATION_MSG
        );
    }

    /**
     * API used to sync the project google drive dir
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function cloudSync(Request $request, $record)
    {
        $project = Project::findOrFail($record->id);

        // $project->checkForUpdatedFilesOnGoogleDrive();
        ProjectGoogleSync::dispatch($project);

        $resp = Response(
            [
                'data' => [
                    'type' => 'projects',
                    'id' => $project->id,
                    'attributes' => ['syncronized' => 'queued'],
                ],
            ],
            200
        );

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }

    /**
     * @param Request $request
     * @param Document $document
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function renderJsonOrDownloadFile(Request $request, Document $document)
    {
        if ($document) {
            // if &download=true in request, the file will be downloaded in the response body
            if ($request->has('download') && $request->input('download')) {
                $document->refresh();
                $filepath = $document->getPathBySize('');
                $filename = $document->file_name;

                $headers = [
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                    'Content-Type' => 'application/octet-stream',
                ];

                return response()->download($filepath, $filename, $headers);
            } else {
                $ret = [
                    'data' => [
                        'type' => 'documents',
                        'id' => $document->id,
                        'attributes' => $document,
                    ],
                ];

                return Utils::renderStandardJsonapiResponse($ret, 200);
            }
        }
    }

    /**
     * #PR16-a: API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     * @param ReportGenerator $reportGenerator
     *
     * @return mixed
     */
    public function generateReport(Request $request, $record, ReportGenerator $reportGenerator)
    {
        try {
            /** @var Project $project */
            $project = Project::findOrFail($record->id);
            $tasksToIncludeInReport = $request->has('tasks') ? explode(',', $request->tasks) : [];
            $project->setTasksToIncludeInReport($tasksToIncludeInReport, $request->input('only_public'));

            $template = $request->template;
            $subtype = $request->has('subtype') ? $request->subtype : REPORT_CORROSION_MAP_SUBTYPE;
            $document = $reportGenerator->reportGenerationProcess($template, $project, $subtype);

            if ($document) {
                // TODO: refact not DRY
                if (\Auth::check()) {
                    $auth_user = \Auth::user();
                    $user_id = $auth_user->id;
                } else {
                    $user_id = $document->author_id ?? 1; // admin
                }

                ReportItem::touchForNewDocument(
                    $document,
                    REPORT_ITEM_TYPE_CORR_MAP_DOC,
                    $user_id,
                    $project->id,
                    [
                        'id' => $document->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, 402, 'Error generating report', $e->getMessage());
        }

        $project->closeAllTasksTemporaryFiles();

        return $this->renderJsonOrDownloadFile($request, $document);
    }

    /**
     * #PR16-b: API used to generate a report from the project (queued)
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function generateReportQueued(Request $request, $record)
    {
        $projectId = $record->id;
        $tasksToIncludeInReport = $request->has('tasks') ? explode(',', $request->tasks) : [];
        $selectOnlyPublicTasks = $request->input('only_public');
        $template = $request->template;
        $subtype = $request->has('subtype') ? $request->subtype : REPORT_CORROSION_MAP_SUBTYPE;
        $userId = null;
        if (\Auth::check()) {
            $auth_user = \Auth::user();
            $userId = $auth_user->id;
        }

        $document = null;
        $reportItem = ReportItem::touchForNewDocument(
            $document,
            ReportItem::getTypeByTemplate($template),
            $userId,
            $projectId
        );

        GenerateCorrosionMapReport::dispatch(
            $projectId,
            $tasksToIncludeInReport,
            $selectOnlyPublicTasks,
            $template,
            $subtype,
            $userId,
            $reportItem->id
        );

        return Utils::renderStandardJsonapiResponse([], 200);
    }

    /**
     * #PR17 /api/v1/project/{project_id}/reports-list
     * API used to get the list of reports name and links from google drive
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function reportsList(Request $request, $project)
    {
        $data_array = [];

        /** @var Project $project */
        $reports_data = $project->getReportsLinks($request->input('page'));
        $data = $reports_data['data'];
        foreach ($data as $report) {
            $tmp = [];
            $tmp['type'] = 'report';
            $tmp['id'] = $report['id'];
            $tmp['attributes'] = $report;
            $data_array[] = $tmp;
        }

        $ret = ['data' => $data_array];
        if (isset($reports_data['meta'])) {
            $ret['meta'] = $reports_data['meta'];
        }
        if (isset($reports_data['links'])) {
            $ret['links'] = $reports_data['links'];
        }

        return Utils::renderStandardJsonapiResponse($ret, 200);
    }

    /**
     * #PR18  api/v1/projects/{record_id}/upload-env-measurement-log
     *
     * API used to upload and parse a sensor log for the environment.
     * A docx report will be also generated and downloaded by the API.
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     * @throws \Throwable
     */
    public function uploadEnvMeasurementLog(Request $request, $record)
    {
        try {
            /** @var Project $project */
            $project = Project::findOrFail($record->id);
            $base64File = $request->data['attributes']['file'];
            $filename = $request->data['attributes']['filename'];
            $file = Document::createUploadedFileFromBase64($base64File, $filename);
            if ($file) {
                $data_source = isset($request->data['attributes']['data_source']) ? utf8_encode(
                    trim($request->data['attributes']['data_source'])
                ) : null;

                $arr = [
                    'data_source' => $data_source,
                ];

                $additional_data = json_encode($arr);
                $document = $project->addDocumentFileDirectly(
                    $file,
                    $filename,
                    MEASUREMENT_FILE_TYPE,
                    REPORT_ENVIRONMENTAL_SUBTYPE,
                    $additional_data
                );
                // $document = $project->getDocument(MEASUREMENT_FILE_TYPE);
                if ($document) {
                    if (\Auth::check()) {
                        $auth_user = \Auth::user();
                        $user_id = $auth_user->id;
                    } else {
                        $user_id = $document->author_id ?? 1; // admin
                    }

                    ReportItem::touchForNewEnvironmentalLog(
                        $document,
                        $user_id,
                        $project->id,
                        [
                            'id' => $document->id,
                            'area' => $data_source,
                            'measurement_interval_dates' => null,
                        ]
                    );

                    ProjectLoadEnvironmentalData::dispatch(
                        $project,
                        $document,
                        $data_source
                    ); // default queue

                    return $this->renderJsonOrDownloadFile($request, $document);
                }
            } else {
                throw new \Exception("Cannot upload the file $filename!");
            }
        } catch (\Exception $e) {
            $msg = __("Error: ':e_msg'!", ['e_msg' => $e->getMessage()]);

            return Utils::jsonAbortWithInternalError(422, 402, 'Error uploading CSV log file', $msg);
        }
    }

    /**
     * #PR19-a  api/v1/projects/{record_id}/generate-environmental-report
     * API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     * @param ReportGenerator $reportGenerator
     *
     * @return mixed
     */
    public function generateEnvironmentalReport(Request $request, $record, ReportGenerator $reportGenerator)
    {
        if (! $request->has('data_source')) {
            return Utils::jsonAbortWithInternalError(
                422,
                402,
                'Error generating report',
                "Mandatory parameter 'data_source' is missing!"
            );
        }

        /** @var Project $project */
        $project = Project::findOrFail($record->id);

        $template = $request->input('template');
        $data_source = $request->input('data_source');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $min_thresholds = [
            'Celsius' => $request->has('temp_min_threshold') ? $request->input('temp_min_threshold') : null,
            'Dew Point' => $request->has('dp_min_threshold') ? $request->input('dp_min_threshold') : null,
            'Humidity' => $request->has('hum_min_threshold') ? $request->input('hum_min_threshold') : null,
        ];

        $project->setCurrentDateStart($date_start);
        $project->setCurrentDateEnd($date_end);
        $project->setCurrentMinThresholds($min_thresholds);
        $project->setCurrentDataSource($data_source);

        try {
            $document = $reportGenerator->reportGenerationProcess($template, $project, REPORT_ENVIRONMENTAL_SUBTYPE);

            if ($document) {
                // TODO: refact not DRY
                if (\Auth::check()) {
                    $auth_user = \Auth::user();
                    $user_id = $auth_user->id;
                } else {
                    $user_id = $document->author_id ?? 1; // admin
                }

                ReportItem::touchForNewDocument(
                    $document,
                    REPORT_ITEM_TYPE_ENVIRONM_DOC,
                    $user_id,
                    $project->id,
                    [
                        'id' => $document->id,
                        'area' => $data_source,
                        'measurement_interval_dates' => [
                            'min' => $date_start,
                            'max' => $date_end,
                        ],
                    ]
                );
            }
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error generating report', $e->getMessage());
        }

        // $filepath = $dg->getRealFinalFilePath();
        // $filename = $dg->getFinalFileName()
        return $this->renderJsonOrDownloadFile($request, $document);
    }

    /**
     * #PR19-b  api/v1/projects/{record_id}/generate-environmental-report-queued
     * API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     * @param ReportGenerator $reportGenerator
     *
     * @return mixed
     */
    public function generateEnvironmentalReportQueued(Request $request, $record, ReportGenerator $reportGenerator)
    {
        if (! $request->has('data_source')) {
            return Utils::jsonAbortWithInternalError(
                422,
                402,
                'Error generating report',
                "Mandatory parameter 'data_source' is missing!"
            );
        }

        /** @var Project $project */
        $project = Project::findOrFail($record->id);

        $template = $request->input('template');
        $data_source = $request->input('data_source');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $min_thresholds = [
            'Celsius' => $request->has('temp_min_threshold') ? $request->input('temp_min_threshold') : null,
            'Dew Point' => $request->has('dp_min_threshold') ? $request->input('dp_min_threshold') : null,
            'Humidity' => $request->has('hum_min_threshold') ? $request->input('hum_min_threshold') : null,
        ];

        $project->setCurrentDateStart($date_start);
        $project->setCurrentDateEnd($date_end);
        $project->setCurrentMinThresholds($min_thresholds);
        $project->setCurrentDataSource($data_source);

        try {
            $document = $reportGenerator->reportGenerationProcess($template, $project, REPORT_ENVIRONMENTAL_SUBTYPE);

            if ($document) {
                // TODO: refact not DRY
                if (\Auth::check()) {
                    $auth_user = \Auth::user();
                    $user_id = $auth_user->id;
                } else {
                    $user_id = $document->author_id ?? 1; // admin
                }

                ReportItem::touchForNewDocument(
                    $document,
                    REPORT_ITEM_TYPE_ENVIRONM_DOC,
                    $user_id,
                    $project->id,
                    [
                        'id' => $document->id,
                        'area' => $data_source,
                        'measurement_interval_dates' => [
                            'min' => $date_start,
                            'max' => $date_end,
                        ],
                    ]
                );
            }
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error generating report', $e->getMessage());
        }

        // $filepath = $dg->getRealFinalFilePath();
        // $filename = $dg->getFinalFileName()
        return $this->renderJsonOrDownloadFile($request, $document);
    }

    /**
     * #PR20  api/v1/projects/{record_id}/env-measurements-logs
     *
     * @param Request $request
     * @param $project
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function envMeasurementsLogs(Request $request, $project)
    {
        /** @var Project $project */
        $reports_data = $project->getMeasurementLogsData($request->input('page'));
        $data = $reports_data['data'];

        $data_array = [];
        foreach ($data as $log) {
            $tmp = [];
            $tmp['id'] = $log['id'];
            $tmp['type'] = 'log';
            $tmp['attributes'] = $log;
            $data_array[] = $tmp;
        }

        $ret = ['data' => $data_array];
        if (isset($reports_data['meta'])) {
            $ret['meta'] = $reports_data['meta'];
        }
        if (isset($reports_data['links'])) {
            $ret['links'] = $reports_data['links'];
        }

        return Utils::renderStandardJsonapiResponse($ret, 200);
    }

    /**
     * #PR21  api/v1/projects/{record_id}/env-measurements-datasources
     *
     * Get all the sources of environmental data
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getDataSources(Request $request, $record)
    {
        try {
            /** @var Project $project */
            $project = Project::findOrFail($record->id);
            $sources = $project->getAllDataSources();
            $data_array = [];
            foreach ($sources as $source) {
                $tmp = [];
                $tmp['type'] = 'data_source';
                $tmp['id'] = md5($source);
                $tmp['attributes'] = [
                    'name' => $source,
                ];

                $data_array[] = $tmp;
            }
            $ret = ['data' => $data_array];

            return Utils::renderStandardJsonapiResponse($ret, 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error generating report', $e->getMessage());
        }
    }

    /**
     * #PR22  api/v1/projects/1/env-log-delete
     *
     * Remove all measurements associated to a specific log file
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function removeDocumentMeasurements(Request $request, $record)
    {
        try {
            /** @var Project $project */
            $project = Project::findOrFail($record->id);
            $document_id = $request->input('document_id');
            if ($project->countMeasurementsByDocument($document_id)) {
                // ..prima rimuovo le misurazioni associate ad un documento...
                $project->deleteMeasurementsByDocument($document_id);
            } else {
                return Utils::jsonAbortWithInternalError(
                    422,
                    100,
                    'Error removing data',
                    'No measurements for this document!'
                );
            }

            // ...poi rimuovo il documento stesso
            /** @var Document $document */
            $document = Document::findOrFail($document_id);
            $project->deleteDocument($document);
            // $document->destroyMe();

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error generating report', $e->getMessage());
        }
    }

    /**
     * #PR23  api/v1/projects/{record_id}/bulk-create-zones
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Throwable
     */
    public function bulkCreateZones(Request $request, $record)
    {
        try {
            $zones = $request->data;
            $this->_zones_persister->persistZones($record, $zones);

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error creating zones', $e->getMessage());
        }
    }

    /**
     * #PR24  api/v1/projects/{record_id}/bulk-delete-zones
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function bulkDeleteZones(Request $request, $record)
    {
        try {
            $zones = $request->data;
            if (! empty($zones)) {
                foreach ($zones as $zone_resource) {
                    $zone = Zone::findOrFail($zone_resource['id']);
                    $zone->delete();
                }
            }

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error deleting zones', $e->getMessage());
        }
    }

    /**
     * #PR28 /api/v1/projects/{record_id}/app-log-structure/{app_log_id}
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getApplicationLogStructure(Request $request, $record, $app_log_id)
    {
        try {
            /** @var ApplicationLog $app_log */
            $app_log = $record->application_logs()->findOrFail($app_log_id);

            return Utils::renderStandardJsonapiResponse(['data' => $app_log->toJsonApi()], 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(
                422,
                $e->getCode(),
                'Error retrieving application log',
                $e->getMessage()
            );
        }
    }

    /**
     * #PR29 /api/v1/projects/{record_id}/app-log-next-id
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getApplicationLogNextProgressiveNumber(Request $request, $record)
    {
        try {
            /** @var Project $record */
            $boat = $record->boat;
            abort_if(is_null($boat), 500, 'This project is not related to any boat');
            $prog = ApplicationLog::getLastInternalProgressiveIDByBoat($boat->id);
            $data = [
                'type' => 'application_logs',
                'id' => time(),
                'attributes' => [
                    'internal_progressive_number' => $prog + 1,
                ],
            ];

            return Utils::renderStandardJsonapiResponse(['data' => $data], 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(
                422,
                $e->getCode(),
                'Error retrieving application log next ID',
                $e->getMessage()
            );
        }
    }

    /**
     * #PR30 (POST) /api/v1/projects/{record_id}/app-log-structure/{app_log_id}
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Throwable
     */
    public function postApplicationLogStructure(RequestApplicationLog $request, $record)
    {
        try {
            /** @var ApplicationLog $app_log */
            $app_log = $record->application_logs()->find($request->input('data.id'));
            if (! $app_log) {
                $app_log = ApplicationLog::create(
                    [
                        'name' => $request->input('data.attributes.name'),
                        'application_type' => $request->input('data.attributes.application_type'),
                        'project_id' => $record->id,
                    ]
                );
            } else {
                if ($app_log->name != $request->input('data.attributes.name')) {
                    $app_log->update(
                        [
                            'name' => $request->input('data.attributes.name'),
                        ]
                    );
                }
            }
            // dobbiamo distinguere tra l'app_log appena creato/recuperato ed il malloppone json passato in POST
            $sections = $request->input('data.attributes.application_log_sections');
            foreach ($sections as $section) {
                // creare uno switch che analizza il tipo, prima però verifichiamo con l'id se abbiamo già la section e con update se è cambiata
                $this->_app_log_persister->persistSection($app_log, $section);
            }

            return Utils::renderStandardJsonapiResponse(['data' => $app_log->toJsonApi()], 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(
                422,
                $e->getCode(),
                'Error uploading application log',
                $e->getMessage()
            );
        }
    }

    /**
     * #PR31 (POST) /api/v1/projects/{record_id}/tasks-statistics
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Throwable
     */
    public function getTasksStatistics(Request $request, $record)
    {
        try {
            $ret = [
                'tasks_num' => $record->getAllTaskNumGroupedByStatus(),
                'authors' => Task::getAllAuthors($record->id),
            ];

            return Utils::renderStandardJsonapiResponse(['data' => $ret], 200);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(
                422,
                $e->getCode(),
                'Error uploading application log',
                $e->getMessage()
            );
        }
    }

    /**
     * #PR32-a  api/v1/projects/{record_id}/generate-application-log-report
     * API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     * @param ReportGenerator $reportGenerator
     *
     * @return mixed
     */
    public function generateApplicationLogReport(Request $request, $record, ReportGenerator $reportGenerator)
    {
        if (! $request->has('application_log_id')) {
            return Utils::jsonAbortWithInternalError(
                422,
                402,
                'Error generating report',
                "Mandatory parameter 'application_log_id' is missing!"
            );
        }

        try {
            $application_log_id = $request->input('application_log_id');
            /** @var ApplicationLog $application_log */
            $application_log = ApplicationLog::findOrFail($application_log_id);
            $record->setCurrentAppLog($application_log);
            $record->setTasksToIncludeInReport($application_log->opened_tasks()->pluck('id')->toArray());

            $template = $request->input('template');
            $document = $reportGenerator->reportGenerationProcess($template, $record, REPORT_APPLOG_SUBTYPE);

            if ($document) {
                // TODO: refact not DRY
                if (\Auth::check()) {
                    $auth_user = \Auth::user();
                    $user_id = $auth_user->id;
                } else {
                    $user_id = $document->author_id ?? 1; // admin
                }

                ReportItem::touchForNewDocument(
                    $document,
                    REPORT_APPLOG_SUBTYPE,
                    $user_id,
                    $record->id,
                    [
                        'id' => $document->id,
                        'app_log_type' => $record->getCurrentAppLogType(),
                        'app_log_name' => $application_log->name,
                        'zones' => $record->getCurrentAppLogZones(),
                    ]
                );
            }
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error generating report', $e->getMessage());
        }

        // $filepath = $dg->getRealFinalFilePath();
        // $filename = $dg->getFinalFileName()
        return $this->renderJsonOrDownloadFile($request, $document);
    }

    /**
     * #PR32-b  api/v1/projects/{record_id}/generate-application-log-report-queued
     * API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function generateApplicationLogReportQueued(Request $request, $record)
    {
        if (! $request->has('application_log_id')) {
            return Utils::jsonAbortWithInternalError(
                422,
                402,
                'Error generating report',
                "Mandatory parameter 'application_log_id' is missing!"
            );
        }

        $projectId = $record->id;
        $userId = null;
        if (\Auth::check()) {
            $auth_user = \Auth::user();
            $userId = $auth_user->id;
        }

        $document = null;
        $reportItem = ReportItem::touchForNewDocument(
            $document,
            REPORT_APPLOG_SUBTYPE,
            $userId,
            $projectId
        );

        GenerateApplicationLogReport::dispatch(
            $projectId,
            $request->get('application_log_id'),
            $request->input('template'),
            REPORT_APPLOG_SUBTYPE,
            $userId,
            $reportItem->id
        );

        return Utils::renderStandardJsonapiResponse([], 200);
    }

    /**
     * #PR33  /api/v1/closed-projects
     * @param Request $request
     *
     * @return mixed
     */
    public function closedProjects(Request $request)
    {
        /** @var User $user */
        $user = \Auth::user();
        if ($user->can(PERMISSION_ADMIN) || $user->can(PERMISSION_BACKEND_MANAGER)) {
            $data = [];
            $sortField = 'updated_at';
            $sortDir = 'desc';
            if ($request->has('sort')) {
                $sortField = $request->get('sort');
                if (! Str::startsWith($sortField, '-')) {
                    $sortDir = 'asc';
                } else {
                    $sortField = substr($sortField, 1);
                }
            }
            $closedProjects = Project::closedProjectsFiltered($request->get('filter'), $sortField, $sortDir);
            if (! empty($closedProjects)) {
                foreach ($closedProjects as $project) {
                    $data['data'][] = [
                        'id' => $project->id,
                        'type' => 'projects',
                        'attributes' => $project,
                    ];
                }
            }

            return Utils::renderStandardJsonapiResponse($data, 200);
        }

        return Utils::jsonAbortWithInternalError(401, 401, 'Authorization denied', "You're not allowed to access this resource.");
    }

    /**
     * #PR34
     * @param Request $request
     * @param $record
     * @param ReportGenerator $reportGenerator
     * @return Response|void
     */
    public function downloadCsv(Request $request, $record, ReportGenerator $reportGenerator)
    {
        try {
            /** @var Project $project */
            $project = Project::findOrFail($record->id);
            $tasksToIncludeInReport = $request->has('tasks') ? explode(',', $request->tasks) : [];
            $filename = $request->get('filename') ?: 'project-'.$record->id.'-csv-report-'.date('Y-m-d-His');
            $project->setTasksToIncludeInReport($tasksToIncludeInReport, $request->input('only_public'));
            $csv = $project->extractCsvFile();
//            $csv->output("$filename.csv");
            return response((string) $csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
            ]);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, 402, 'Error generating report', $e->getMessage());
        }
    }
}
