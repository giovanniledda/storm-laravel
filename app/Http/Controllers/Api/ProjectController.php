<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestProjectChangeType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use const PROJECT_STATUS_CLOSED;
use Validator;
use Illuminate\Validation\Rule;
use Net7\Documents\Document;
use App\Utils\Utils;
use Net7\Logging\models\Logs as Log;
use App\Jobs\ProjectGoogleSync;
use Net7\DocsGenerator\DocsGenerator;

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

    public function cloudSync(Request $request, $record){

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
     * API used to generate a report from the project
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */


    public function generateReport(Request $request, $record){
        $project = Project::findOrFail($record->id);
        $tasks = $request->tasks;
        $template = $request->template;

        $project->setTasksToIncludeInReport(explode(',', $tasks));

        // TODO: take it from input
        $template = 'SampleReport';

        try {
            $dg = new DocsGenerator($template, $project);
        } catch (\Exception $e) {
            // TODO: return error $e->getMessage()
        }

        if (!$dg->checkTemplateCategory()) {
            $msg = __("Template :name not valid (there's no such a Model on DB)!", ['name' => $template]);
           // TODO return error $msg
        }

        // ...e che ci sia il template associato nel filesystem.
        try {
            $dg->checkIfTemplateFileExistsWithTemplateObjectCheck(true);
        } catch (FileNotFoundException $e) {
            $msg = __("Template :name not found (you're searching on ':e_msg')!", ['name' => $template, 'e_msg' => $e->getMessage()]);
         // TODO return error  $msg
        }


        try {
            $dg->startProcess();
        } catch (\Exception $e) {
           // TODO return error $e->getMessage()
        }

        $project->closeAllTasksTemporaryFiles();

        $filepath = $dg->getRealFinalFilePath();
        $headers = ['Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'];
        return response()
            ->download($filepath, $dg->getFinalFileName(), $headers);

    }

}
