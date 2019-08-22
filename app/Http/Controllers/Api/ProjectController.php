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

    public function addDocument(Request $request, $related) {

        $project = json_decode($related, true);
        $project = Project::find($project['id']);
        $rules = [
            'type' => ['required', Rule::in([
                    Document::GENERIC_DOCUMENT_TYPE,
                    Document::DETAILED_IMAGE_TYPE,
                    Document::GENERIC_IMAGE_TYPE,
                    Document::ADDITIONAL_IMAGE_TYPE
                ])]
        ];

        $validator = Validator::make($request->data['attributes'], $rules);

        if ($validator->passes()) {
            $type = $request->data['attributes']['type'];
            $title = $request->data['attributes']['title'];
            $base64File = $request->data['attributes']['file'];
            $filename = $request->data['attributes']['filename']; 
            $file = Document::createUploadedFileFromBase64($base64File, $filename); 
            $doc = new Document([
                'title' => $title,
                'file' => $file,
            ]);

            // $doc->save();
            $project->addDocumentWithType($doc, $type);
            $ret = \App\Utils\Utils::renderDocumentResponce('projects', $doc); 
            $resp = Response($ret, 200);
        } else {
           $contents_errors = \App\Utils\Utils::renderDocumentErrors($validator->errors()->all());
           $resp = Response(['errors' =>$contents_errors], 422);
        }
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    } 
}
