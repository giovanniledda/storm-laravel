<?php

namespace App\Http\Controllers\Api;

use \Net7\Documents\DocumentsController as BaseController;
use Illuminate\Http\Request;

class DocumentsController extends BaseController {

    public function show (Request $request){

        $document = $request->record;
        $entity = $document->documentable;

        if (get_class($entity) == 'App\Project') {
            try {
                //   $url = $entity->getDocumentFromDropbox($document);
                // return response()->redirectTo($url);

                return $entity->getDocumentFromGoogle($document);


            } catch ( \Spatie\Dropbox\Exceptions\BadRequest $e){
                $contents_errors = $this->renderDocumentErrors([$e->getMessage()]);
                $resp = Response(['errors' =>$contents_errors], 404);
                $resp->header('Content-Type', 'application/json');
                return $resp;
            }

        } else {
            return parent::show($request);
        }
    }

}
