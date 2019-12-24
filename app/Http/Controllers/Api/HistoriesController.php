<?php

namespace App\Http\Controllers\Api;

use App\Comment;
use App\History;
use App\Jobs\NotifyTaskUpdates;
use App\Jobs\ProjectLoadEnvironmentalData;
use App\Notifications\TaskCreated;
use App\Zone;
use function __;
use function array_key_exists;
use function explode;
use function in_array;
use function json_decode;
use function md5;
use function notify;
use function response;
use function trim;
use function view;
use const MEASUREMENT_FILE_TYPE;
use const PROJECT_STATUS_CLOSED;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\User;
use Validator;
use Net7\Documents\Document;
use Net7\Logging\models\Logs as Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestProjectChangeType;
use App\Project;
use App\Utils\Utils;
use App\Jobs\ProjectGoogleSync;
use Net7\DocsGenerator\DocsGenerator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use const PROJECT_STATUSES;
use const REPORT_ENVIRONMENTAL_SUBTYPE;

class HistoriesController extends Controller
{

    /**
     *
     * #H02  api/v1/histories/{record_id}/image-delete
     *
     * Remove an image associated to a specific history item
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function removeImageDocument(Request $request, $record)
    {
        try {
            /** @var History $history */
            $history = History::findOrFail($record->id);
            $document_id = $request->input('document_id');

            // ...poi rimuovo il documento stesso
            /** @var Document $document */
            $document = Document::findOrFail($document_id);
            $history->deleteDocument($document);

            return Utils::renderStandardJsonapiResponse([], 204);

        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), "Error removing image", $e->getMessage());
        }
    }



    /**
     *
     * #H03  api/v1/histories/{record_id}/add-comment
     *
     * Add a comment to a specific history item
     *
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function addComment(Request $request, $record)
    {
        try {
            if (\Auth::check() && $request->has('data.attributes.body')) {
                /** @var History $history */
                $history = History::findOrFail($record->id);
                $body = $request->input('data.attributes.body');
                $c = Comment::create([
                    'body' => $body,
                    'author_id' => \Auth::user()->id
                ]);
                $history->comments()->save($c);
            }

            return Utils::renderStandardJsonapiResponse([], 204);

        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), "Error creating comment", $e->getMessage());
        }
    }
}

