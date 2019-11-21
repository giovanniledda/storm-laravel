<?php

namespace App\Http\Controllers\Api;

use function __;
use function json_decode;
use function notify;
use function view;
use const MEASUREMENT_FILE_TYPE;
use const PROJECT_STATUS_CLOSED;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\User;
use Validator;
use Net7\Documents\Document;
use Net7\Logging\models\Logs as Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestProjectChangeType;
use App\Project;
use App\Utils\Utils;
use App\Jobs\ProjectGoogleSync;
use Net7\DocsGenerator\DocsGenerator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ProjectController extends Controller
{


    /**
     * Ritorna i possibili stati usati nei progetti.
     * @param Request $request
     * @return type
     */
    public function statuses(Request $request)
    {
        $resp = Response(["data" => [
            "type" => "projects",
            "attributes" => ["statuses" => PROJECT_STATUSES]
        ]], 200);

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
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
            array_push($data, [
                "type" => "projects",
                "attributes" => ['event' => $history['event_body']]]);
        }
        $resp = Response(["data" => $data], 200);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;

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
            $ret = ['data' => [
                'type' => 'projects',
                'id' => $data['id'],
                'attributes' => [
                    'status' => PROJECT_STATUS_CLOSED,
                    'force' => $force,
                    'tasks' => $closeResponse['tasks']
                ]
            ]];
            return Response($ret, 202);
        } else {
            $ret = ['data' => [
                'type' => 'projects',
                'id' => $data['id'],
                'attributes' => [
                    'status' => $data['project_status'],
                    'force' => $force,
                    'tasks' => $closeResponse['tasks']
                ]
            ]];
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
        $project = Project::findOrFail($record->id);
        if ($project->project_status == PROJECT_STATUS_CLOSED) {
            return Utils::jsonAbortWithInternalError(422, 130, PROJECT_TYPE_API_VALIDATION_TITLE, PROJECT_TYPE_API_PROJECT_CLOSED_MSG);
        }
        if ($type = $request->input('data.attributes.type')) {
            if ($type != $project->project_type) {
                $newProject = $project->replicate();
                $newProject->project_type = $type;
                $newProject->project_status = PROJECT_STATUS_IN_SITE;
                $newProject->save();

                // copio gli utenti associati
                $project->transferMyUsersToProject($newProject);

                // archivio il vecchio
                $project->close(1);

                $ret = ['data' => [
                    'type' => 'projects',
                    'id' => $newProject->id,
                    'attributes' => $newProject
                ]];
                return Utils::renderStandardJsonapiResponse($ret, 200);

            } else {
                return Utils::jsonAbortWithInternalError(422, 130, PROJECT_TYPE_API_VALIDATION_TITLE, PROJECT_TYPE_API_NO_ACTION_MSG);
            }
        }
        return Utils::jsonAbortWithInternalError(422, 130, PROJECT_TYPE_API_VALIDATION_TITLE, PROJECT_TYPE_API_VALIDATION_MSG);
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

        $resp = Response(['data' => [
            'type' => 'projects',
            'id' => $project->id,
            "attributes" => ["syncronized" => "queued"]
        ]], 200);

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }


    /**
     * API used to get the list of reports name and links from google drive
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */

    public function reportsList(Request $request, $project)
    {

        // $project = Project::findOrFail($record->id);

        $data = $project->getReportsLinks();

        $resp = Response(['data' => [
            $data
        ]], 200);

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }


    /**
     * API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function generateReport(Request $request, $record)
    {
        $project = Project::findOrFail($record->id);
        $tasks = $request->tasks;
        $template = $request->template;

        $project->setTasksToIncludeInReport(explode(',', $tasks));

        // TODO: take it from input
        // $template = 'corrosion_map';

        try {
            $dg = new DocsGenerator($template, $project);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, 402, "Error instantiating DocsGenerator", $e->getMessage());
        }

        if (isset($dg) && !$dg->checkTemplateCategory()) {
            $msg = __("Template :name not valid (there's no such a Model on DB)!", ['name' => $template]);
            return Utils::jsonAbortWithInternalError(422, 402, "Error checking template", $msg);
        }

        // ...e che ci sia il template associato nel filesystem.
        try {
            $dg->checkIfTemplateFileExistsWithTemplateObjectCheck(true);
        } catch (FileNotFoundException $e) {
            $msg = __("Template :name not found (you're searching on ':e_msg')!", ['name' => $template, 'e_msg' => $e->getMessage()]);
            return Utils::jsonAbortWithInternalError(422, 402, "Error checking template existance", $msg);
        }

        try {
            $document = $dg->startProcess();
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, 402, "Error generatig report", $e->getMessage());
        }

        $project->closeAllTasksTemporaryFiles();

        // $filepath = $dg->getRealFinalFilePath();
        // $filename = $dg->getFinalFileName()
        if ($document) {
            $document->refresh();
            $filepath = $document->getPathBySize('');
            $filename = $document->file_name;

            $headers = ['Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', "Content-Type" => "application/octet-stream"];
            return response()
                ->download($filepath, $filename, $headers);

        }
    }

    /**
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
                $project->addDocumentFileDirectly($file, 'log_measurements_temp_dp_hum.txt', MEASUREMENT_FILE_TYPE);
            }
            $document = $project->getDocument(MEASUREMENT_FILE_TYPE);
            if ($document) {
                $file_path = $project->getDocumentMediaFilePath(MEASUREMENT_FILE_TYPE);
                $array = \Net7\EnvironmentalMeasurement\Utils::convertCsvInAssociativeArray($file_path);
                $min_thresholds = [
//                    'Celsius' => $request->has('temp_min_threshold') ? $request->input('temp_min_threshold') : null,
//                    'Dew Point' => $request->has('dp_min_threshold') ? $request->input('dp_min_threshold') : null,
//                    'Humidity' => $request->has('hum_min_threshold') ? $request->input('hum_min_threshold') : null,

//                    'Celsius' => isset($request->data['attributes']['temp_min_threshold']) ? $request->data['attributes']['temp_min_threshold'] : null,
//                    'Dew Point' => isset($request->data['attributes']['dp_min_threshold']) ? $request->data['attributes']['dp_min_threshold'] : null,
//                    'Humidity' => isset($request->data['attributes']['hum_min_threshold']) ? $request->data['attributes']['hum_min_threshold'] : null,
                ];
                $project->translateMeasurementsInputForTempDPHumSensor($array, 'STORM - Web App Frontend', $min_thresholds);

                $ret = ['data' => [
                    'type' => 'documents',
                    'id' => $document->id,
                    'attributes' => [
                        'name' => $document->title,
                        'created-at' => $document->created_at,
                        'updated-at' => $document->updated_at
                    ]
                ]];

                return Utils::renderStandardJsonapiResponse($ret, 200);
            }
        } catch (\Exception $e) {
            $msg = __("Error: ':e_msg'!", ['e_msg' => $e->getMessage()]);
            return Utils::jsonAbortWithInternalError(422, 402, "Error uploading CSV log file", $msg);
        }
    }

}
