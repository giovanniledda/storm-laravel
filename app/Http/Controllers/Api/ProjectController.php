<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use Validator;
use Illuminate\Validation\Rule;
use App\Document;
use App\Utils\Utils;
use Net7\Logging\models\Logs as Log;

class ProjectController extends Controller {


    /**
     * Ritorna i possibili stati usati nei progetti.
     * @param Request $request
     * @return type
     */
    public function statuses(Request $request) {
        $resp = Response(["data" => [
                "type" => "projects",
                "attributes" => ["project-statuses" => PROJECT_STATUSES]
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
    public function history(Request $request, $related) {
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

    public function close(Request $request, $related) {
        $data = json_decode($related, true);
        // indica se chiudere il progetto mettendo i task o no.
        $force   =  isset($request->data['attributes']['force']) ? $request->data['attributes']['force'] : 0;
        $closeResponse = Project::findOrFail($data['id'])->close($force);

        Log::info("i'm here", $request);


        if ($closeResponse['success']) {
            $ret = ['data' => [
                    'type' => 'projects',
                    'id' => $data['id'],
                    'attributes' => [
                        'status' => PROJECT_STATUS_CLOSED,
                        'force'  => $force,
                        'tasks'  => $closeResponse['tasks']
                    ]
            ]];
           return Response($ret, 202);
        } else {
            $ret = ['data' => [
                    'type' => 'projects',
                    'id' => $data['id'],
                    'attributes' => [
                        'status' => $data['project_status'],
                        'force'  => $force,
                        'tasks'  => $closeResponse['tasks']
                    ]
            ]];
            return Response($ret, 200);
        }

    }
}
