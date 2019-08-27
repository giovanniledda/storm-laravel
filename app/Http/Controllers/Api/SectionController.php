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
use Net7\Documents\Document;
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


}
