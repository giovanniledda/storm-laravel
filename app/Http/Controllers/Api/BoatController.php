<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Boat;
use Validator;
use Illuminate\Validation\Rule;
use App\Document;
use App\Utils\Utils;


class BoatController extends Controller {

    public function addDocument(Request $request, $related) {

        $boat = json_decode($related, true);
        $boat = Boat::find($boat['id']);

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

            $boat->addDocumentWithType($doc, $type);

            $ret = ['data' => [
                    'id' => $doc->id,
            ]];
            $resp = Response($ret, 200);
        } else {
            
            $contents_errors = \App\Utils\Utils::renderDocumentErrors($validator->errors()->all());
            $resp = Response(['errors' =>$contents_errors], 422);
            
        }

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }

}
