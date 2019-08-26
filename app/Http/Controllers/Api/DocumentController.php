<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Document;
use Exception;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    public function show(Request $request){

        $document = $request->record;
        if ($request->size){
            //TODO:  get the related image
         return response()->download( $document->getFirstMediaPath('documents', $request->size), $document->title);
        }
        return $document->getFirstMedia('documents');

    }

    public function create(Request $request){
        try {

        $rules = [
            'type'=>['required', Rule::in([
                Document::GENERIC_DOCUMENT_TYPE,
                Document::DETAILED_IMAGE_TYPE,
                Document::GENERIC_IMAGE_TYPE,
                Document::ADDITIONAL_IMAGE_TYPE
                ])]
        ];

            $validator = Validator::make($request->data['attributes'], $rules);

            if (!$validator->passes()) {

                throw new ValidationException();
            }

            $type = $request->data['attributes']['type'];
            $title = $request->data['attributes']['title'];
            $base64File = $request->data['attributes']['file'];
            $filename = $request->data['attributes']['filename'];
            $entity_type = $request->data['attributes']['entity_type'];
            $entity_id = $request->data['attributes']['entity_id'];

            $file = Document::createUploadedFileFromBase64( $base64File, $filename);
            $doc = new Document([
                'title' => $title,
                'file' => $file,
            ]);


            switch ($entity_type){
                case DOCUMENT_RELATED_ENTITY_PROJECT:
                    $entity = \App\Project::findOrFail($entity_id);
                break;

                case DOCUMENT_RELATED_ENTITY_BOAT:
                    $entity = \App\Boat::findOrFail($entity_id);
                break;

                case DOCUMENT_RELATED_ENTITY_SECTION:
                    $entity = \App\Section::findOrFail($entity_id);
                break;

                case DOCUMENT_RELATED_ENTITY_TASK:
                    $entity = \App\Task::findOrFail($entity_id);
                break;

                default:
                    throw new Exception('Unknown entity_type value');
                break;

            }
            $entity->addDocumentWithType($doc, $type);
            $ret = \App\Utils\Utils::renderDocumentResponse('tasks', $doc);

            $resp = Response($ret , 200);

        } catch (ModelNotFoundException $e) {
            $resp = Response(['errors' => 'Entity of type '.$entity_type.' with ID  '. $entity_id.' doesn\'t exist'], 422);
        } catch (ValidationException $e){

            $contents_errors = \App\Utils\Utils::renderDocumentErrors($validator->errors()->all());
            $resp = Response(['errors' =>$contents_errors], 422);

        } catch (Exception $e){
            $resp = Response(['errors' =>$e->getMessage()], 422);

        }

            $resp->header('Content-Type', 'application/vnd.api+json');
            return $resp;

    }

}


