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
        $record->markAsRead();
        return Utils::renderStandardJsonapiResponse([], 204);
    }

}
