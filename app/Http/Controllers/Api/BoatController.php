<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Boat;
use Validator;
use App\Document;


class BoatController extends Controller
{

    public function addDocument(Request $request, $related){

        $boat = json_decode($related, true);
        $boat = Boat::find($boat['id']);

        $type = $request->type;
        $title = $request->title;
        $base64File = $request->file;
        $filename = $request->filename;

        $file = Document::createUploadedFileFromBase64( $base64File, $filename);

        $doc = new Document([
            'title' => $title,
            'file' => $file,
        ]);

        $boat->addDocumentWithType($doc, $type);

        $ret = ['data' => [
            'id' => $doc->id,
        ]];
        return new Response($ret, 201);


    }

}



