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

        $type = $request->type;
        $title = $request->title;
        $base64File = $request->file;
        $filename = $request->filename;

        $file = Document::createUploadedFileFromBase64( $base64File, $filename);

        $doc = new Document([
            'title' => $title,
            'file' => $file,
        ]);

        $section->addDocumentWithType($doc, $type);

        $ret = ['data' => [
            'id' => $doc->id,
        ]];
        return new Response($ret, 201);


    }

}



