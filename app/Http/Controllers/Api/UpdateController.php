<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use function get_class_methods;
use Illuminate\Http\Request;
use App\Utils\Utils;
use function is_object;

class UpdateController extends Controller
{

    /**
     * Mark the update as read
     *
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function markAsRead(Request $request, $record)
    {

        // TODO: se volessimo restituire senza body
//        $record->markAsRead();
//        return Utils::renderStandardJsonapiResponse([], 204);

        $updated = $record->markAsRead();
        if (is_object($updated)) {
            $ret = ['data' => [
                'type' => 'updates',
                'id' => $record->id,
                'attributes' => $updated
            ]];
            return Utils::renderStandardJsonapiResponse($ret, 200);
        }
        return Utils::jsonAbortWithInternalError(404, 404, 'Resource not found', "No notification with ID {$record->id}");
    }

}
