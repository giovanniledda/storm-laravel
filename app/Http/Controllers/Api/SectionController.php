<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Section;
use Validator;
use Illuminate\Validation\Rule;
use App\Document;
use App\Utils\Utils;

class SectionController extends Controller {
    /**
     *  $rules = [
      'type'=>['required',Rule::in([
      Document::GENERIC_DOCUMENT_TYPE,
      Document::DETAILED_IMAGE_TYPE,
      Document::GENERIC_IMAGE_TYPE,
      Document::ADDITIONAL_IMAGE_TYPE
      ])]
      ];

      $validator = Validator::make($request->data['attributes'], $rules);
      if ($validator->passes()) {
     */

    /**
     * Inserisce un documento per la sezione
     * @param Request $request
     * @param type $related
     * @return type
     */
    public function addDocument(Request $request, $related) {

        $section = json_decode($related, true);
        $section = Section::find($section['id']);

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
            $section->addDocumentWithType($doc, $type); 
            $ret = \App\Utils\Utils::renderDocumentResponce('sections', $doc); 
            $resp = Response($ret, 200);
        } else {
            $resp = Response($validator->errors()->all() , 422);
        }
 
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }

}
