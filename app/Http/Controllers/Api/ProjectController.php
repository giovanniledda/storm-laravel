<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;

class ProjectController extends Controller
{
   
    public function statuses(Request $request) { 
        $resp = Response(["data"=>[
             "type"=>"projects",
             "attributes" =>["project-statuses"=>PROJECT_STATUSES] 
        ]], 201);

        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;
    }
}



 