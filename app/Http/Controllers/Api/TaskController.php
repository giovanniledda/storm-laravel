<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Task;
use App\User;
use Net7\Documents\Document;
use Illuminate\Validation\Rule;
 use Validator;
use Illuminate\Http\UploadedFile;
use App\Utils\Utils;

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
        $histories = Task::find($task['id'])->history()->orderBy('event_date', 'DESC')->get()->toArray();
        $data = [];
         
        
        foreach ($histories as $history) {
             $history_data =array_merge( json_decode($history['event_body'], true) , 
                     ['event_date'=>$history['event_date']]
                     );
            
            
            array_push($data, [
                "type"=>"history" ,
                "attributes"=>$history_data]);
        }
        $resp = Response(["data"=>$data], 200);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;

      //  exit();
    }

}



