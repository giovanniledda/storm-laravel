<?php

namespace App\Http\Controllers\Api;

use \Net7\Documents\DocumentsController as BaseController;
use Illuminate\Http\Request;

class DocumentsController extends BaseController {

    public function show (Request $request){

        $document = $request->record;
        $entity = $document->documentable->first();

        if (get_class($entity) == 'App\Project') {
            try {
              $url = $entity->getDocumentFromDropbox($document);
            } catch ( \Spatie\Dropbox\Exceptions\BadRequest $e){
                $contents_errors = $this->renderDocumentErrors([$e->getMessage()]);
                $resp = Response(['errors' =>$contents_errors], 404);
                $resp->header('Content-Type', 'application/json');
                return $resp;

            }
            $a = 'd';
            return response()->redirectTo($url);
        } else {
            return parent::show($request);
        }
        }


}
