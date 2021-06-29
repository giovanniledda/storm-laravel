<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\User;
use App\Utils\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Net7\Documents\Document;
use Validator;

class SectionController extends Controller
{
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
