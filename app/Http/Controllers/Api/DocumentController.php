<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;

class DocumentController extends Controller
{
    public function show(Request $request){

        $document = $request->record;
        if ($request->size){
            //TODO:  get the related image
            return $document->getFirstMedia('thumb');
        }
        return $document->getFirstMedia('documents');

    }
}
