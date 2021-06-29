<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use App\Utils\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuggestionController extends Controller
{
    /**
     * @param Request $request
     * @param $record
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function updateCounter(Request $request, $record)
    {
        try {
            /** @var Suggestion $suggestion */
            $suggestion = Suggestion::findOrFail($request->input('suggestion_id'));
            $suggestion->update(
                [
                    'use_counter' =>  $suggestion->use_counter + 1,
                ]
            );

            return Utils::renderStandardJsonapiResponse([], 204);
        } catch (\Exception $e) {
            return Utils::jsonAbortWithInternalError(422, $e->getCode(), 'Error incrementing counter', $e->getMessage());
        }
    }
}
