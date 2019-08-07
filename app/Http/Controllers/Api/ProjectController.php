<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use Validator;

class ProjectController extends Controller
{
   
    
    /**
     * Ritorna i possibili stati usati nei progetti.
     * @param Request $request
     * @return type
     */
    public function statuses(Request $request) { 
        $resp = Response(["data"=>[
             "type"=>"projects",
             "attributes" =>["project-statuses"=>PROJECT_STATUSES] 
        ]], 200);

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }
     
    public function history(Request $request, $related) {
        $project = json_decode($related, true);
        $histories =  Project::find($project['id'])->history()->get()->toArray();
        $data = [];     
        foreach ($histories as $history) {
            array_push($data, [ 
                "type"=>"projects" , 
                "attributes"=>['event'=>$history['event_body']]]);
        }
        $resp = Response(["data"=>$data], 200);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
         
      //  exit();
    }
}



 