<?php
/**
 * NOT IN USE
 * serve per ottenere la lista delle log
 */
namespace Net7\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
// use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Validator;
use Exception;
use Net7\Documents\models\Documents;

class DocumentsController extends Controller
{
    /**
     * restituisce l'elenco dei documenti
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
      return ['foo'];
    }


    public function show (Request $request){

        $document = $request->record;
        if ($request->size){
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


            $json_api = config('net7documents.json_api');

            $validator = Validator::make($request->data['attributes'], $rules);

            if (!$validator->passes()) {

                throw new ValidationException('');
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


            $klass= '\App\\' . ucfirst($entity_type);

           if (!class_exists($klass)){
            throw new Exception('Entity of type '. $entity_type. ' doesn\'t exist');
           }

            $entity = $klass::find($entity_id);

            if (!$entity){
                throw new Exception('Entity of type '. $entity_type.' with ID '. $entity_id.' doesn\'t exist');
            }
            $entity->addDocumentWithType($doc, $type);

            $ret = $this->renderDocumentResponse( $type, $doc);

            $resp = Response($ret , 200);

        } catch (ModelNotFoundException $e) {

            $contents_errors = $this->renderDocumentErrors([$e->getMessage()]);
            $resp = Response(['errors' =>$contents_errors], 422);
            // $resp = Response(['errors' => 'Entity of type '.$entity_type.' with ID  '. $entity_id.' doesn\'t exist'], 422);
        } catch (ValidationException $e){

            // $contents_errors = [];

            // foreach ($validator->errors()->all() as $error) {
            //     array_push($contents_errors, [
            //         'status' => 422,
            //         'title' => "Unprocessable Entity",
            //         'detail' => $error,
            //         'source'=>['source'=>['pointer'=> '/data']]
            //     ]);
            // }


            $contents_errors = $this->renderDocumentErrors($validator->errors()->all());
            $resp = Response(['errors' =>$contents_errors], 422);

        } catch (Exception $e){
            $contents_errors = $this->renderDocumentErrors([$e->getMessage()]);
            $resp = Response(['errors' =>$contents_errors], 422);

        }
            $resp->header('Content-Type', 'application/json');
            return $resp;

    }




    /**
     * renderizza una risposta standard api dati gli errori di validazione.
     * @param type $errors
     * @return array
     */
    public  function renderDocumentErrors($errors) {
        $e = [];
        foreach ($errors as $error) {
            array_push($e, [
                'status' => 422,
                'title' => "Unprocessable Entity",
                'detail'=>$error,
                'source'=>['source'=>['pointer'=> '/data']]
            ]);
        }
        return $e;
    }

    /**
     * ritorna una risposta jsonApi standard per la creazione dei documenti
     * @param type $resource
     * @param type $doc
     */
    public  function renderDocumentResponse($resource, $doc) {
          $ret = ['data' => [
                    'type' => $resource,
                    'id' => $doc->id,
                    'attributes' => [
                        'name' => $doc->title,
                        'created-at' => $doc->created_at,
                        'updated-at' => $doc->updated_at
                    ]
            ]];
          return $ret;
    }

}
