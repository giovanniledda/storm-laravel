<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Section;
use Validator;
use App\Document;


class SectionController extends Controller
{

    public function addDocument(Request $request, $related){

        $section = json_decode($related, true);
        $section = Section::find($section['id']);

        $type = $request->data['attributes']['type'];
        $title = $request->data['attributes']['title'];
        $base64File = $request->data['attributes']['file'];
        $filename = $request->data['attributes']['filename'];

        $file = Document::createUploadedFileFromBase64( $base64File, $filename);

        $doc = new Document([
            'title' => $title,
            'file' => $file,
        ]);

        $section->addDocumentWithType($doc, $type);

        $ret = ['data' => [
            'id' => $doc->id,
        ]];
        $resp = Response($ret , 200);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }

}



