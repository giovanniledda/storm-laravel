<?php

namespace App\Http\Controllers\Api;

use App\Utils\Utils;
use Net7\Documents\Document;
use function base64_encode;
use function file_get_contents;
use \Net7\Documents\DocumentsController as BaseController;
use Illuminate\Http\Request;
use function response;

class DocumentsController extends BaseController
{

    /**
     * @param Request $request
     */
    private static function __getDocumentToShow(Request $request)
    {

    }

    public function show(Request $request)
    {
        $document = $request->record;
        $entity = $document->documentable;

        if (get_class($entity) == 'App\Project' && env('USE_GOOGLE_DRIVE')) {
            try {
                //   $url = $entity->getDocumentFromDropbox($document);
                // return response()->redirectTo($url);

                return $entity->getDocumentFromGoogle($document);
            } catch (\Spatie\Dropbox\Exceptions\BadRequest $e) {
                $contents_errors = $this->renderDocumentErrors([$e->getMessage()]);
                $resp = Response(['errors' => $contents_errors], 404);
                $resp->header('Content-Type', 'application/json');
                return $resp;
            }

        } else {
            return parent::show($request);
        }
    }


    public function showBase64(Request $request)
    {
        $document = $request->record;
        $media = $document->getRelatedMedia();

        if (is_object($media)) {

            $file_path = $media->getPath();
            $file = file_get_contents($file_path);
            $base64_data = [
                'base64' => base64_encode($file)
            ];
            $ret = ['data' => [
                'type' => 'documents',
                'id' => $media->id,
                'attributes' => $base64_data
            ]];
            return Utils::renderStandardJsonapiResponse($ret, 200);
        }
        return Utils::jsonAbortWithInternalError(404, 404, 'Resource not found', "No document with ID {$request->record}");
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function downloadDocumentWeb(Request $request)
    {
        /** @var Document $document */
        $document = Document::findOrFail($request->documentId);
        if ($request->size) {
            try {
                $path = $document->getPathBySize($request->size);
            } catch( \Spatie\MediaLibrary\Exceptions\InvalidConversion $e) {
                $contents_errors = [$e->getMessage()];
                $resp = Response(['errors' =>$contents_errors], 404);
                $resp->header('Content-Type', 'application/json');
                return $resp;
            }
            return response()->download($path);
        }
        return $document->getRelatedMedia();
    }
}
