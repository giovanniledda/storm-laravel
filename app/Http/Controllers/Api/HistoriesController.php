<?php

namespace App\Http\Controllers\Api;

use App\Comment;
use App\History;
use App\Http\Controllers\Controller;
use App\Utils\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Net7\Documents\Document;

class HistoriesController extends Controller
{
    /**
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
            $history->updateLastEdit();

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error removing image', $e->getMessage());
        }
    }

    /**
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
                    'author_id' => \Auth::user()->id,
                ]);
                $history->comments()->save($c);
                $history->updateLastEdit();
            }

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error creating comment', $e->getMessage());
        }
    }
}
