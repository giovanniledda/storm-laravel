<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestProjectChangeType;
use App\Http\Requests\RequestUserUpdatePhoto;
use App\Models\Project;
use App\Models\User;
use App\Utils\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Net7\Documents\Document;
use Net7\Logging\models\Logs as Log;
use const PROJECT_STATUS_CLOSED;
use const USER_PHOTO_API_NO_DOC_MSG;
use Validator;

class UserController extends Controller
{
    /**
     * Upload and update the user profile's photo
     *
     * @param RequestUserUpdatePhoto $request
     * @param $record
     *
     * @return mixed
     */
    public function updatePhoto(RequestUserUpdatePhoto $request, $record)
    {
        $base64File = $request->data['attributes']['file'];
        $filename = $request->data['attributes']['filename'];

        $file = Document::createUploadedFileFromBase64($base64File, $filename);

        if ($file) {
            if ($record->hasProfilePhoto()) {
                $doc = $record->getProfilePhotoDocument();
                $record->updateDocument($doc, $file);
            } else {
                $doc = new Document([
                    'title' => "Profile photo for user {$record->id}",
                    'file' => $file,
                ]);
                $record->addDocumentWithType($doc, Document::GENERIC_IMAGE_TYPE);
            }

            $ret = ['data' => [
                'type' => 'documents',
                'id' => $doc->id,
                'attributes' => [
                    'name' => $doc->title,
                    'created-at' => $doc->created_at,
                    'updated-at' => $doc->updated_at,
                ],
            ]];

            return Utils::renderStandardJsonapiResponse($ret, 200);
        }

        return Utils::jsonAbortWithInternalError(500, 500, USER_PHOTO_API_NO_DOC_TITLE, USER_PHOTO_API_NO_DOC_MSG);
    }

    public function getVersion()
    {
        $ret = ['data' => [
                'type' => 'version',
                'id' => getenv('VERSION'),
                'attributes' => [
                    'version' => getenv('VERSION'),

                ],
            ]];

        return Utils::renderStandardJsonapiResponse($ret, 200);
    }
}
