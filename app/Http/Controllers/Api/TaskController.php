<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;

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
}



 