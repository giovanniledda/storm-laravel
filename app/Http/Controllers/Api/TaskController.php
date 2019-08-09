<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Task;
use App\User;
use Validator;


use App\Document;
use Illuminate\Http\UploadedFile;


class TaskController extends Controller
{

    public function statuses(Request $request) {
        $resp = new Response(["data"=>[
             "type"=>"tasks",
             "attributes" =>["task-statuses"=>TASKS_STATUSES]

        ]], 201);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }

    public function history(Request $request, $related) {
        $task = json_decode($related, true);
        $histories = Task::find($task['id'])->history()->get()->toArray();
        $data = [];
        foreach ($histories as $history) {
            array_push($data, [
                "type"=>"tasks" ,
                "attributes"=>['event'=>$history['event_body']]]);
        }
        $resp = Response(["data"=>$data], 200);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;

      //  exit();
    }

    public function addDocument(Request $request, $related){
        // $task = Task::find($request->record);

        $task = json_decode($related, true);
        $task = Task::find($task['id']);

        $type = $request->type;
        $title = $request->title;
        $base64File = $request->file;
        $filename = $request->filename;

        $file = Document::createUploadedFileFromBase64( $base64File, $filename);

        $doc = new Document([
            'title' => $title,
            'file' => $file,
        ]);

        // $doc->save();
        $task->addDocumentWithType($doc, $type);

        $ret = ['data' => [
            'id' => $doc->id,
        ]];
        return new Response($ret, 201);


    }


}



