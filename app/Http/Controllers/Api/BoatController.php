<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\User;
use App\Boat;
use App\Profession;
use App\BoatUser;
use Validator;
use App\Utils\Utils;


class BoatController extends Controller
{

    /**
     * Inserisce un utente in boatUsers e lo caricica come owner
     * @param Request $request
     * @param type $related
     * @return type
     */
    public function owner(Request $request, $related)
    {
        $boat = json_decode($related, true);
        $owner = Profession::where('slug', '=', 'owner')->first();

        $user_id = $request->data['attributes']['user_id'];

        /**
         * @todo aggiungere validatore per user_id
         */

        /**
         * @todo aggiungere il controllo esistenza sia per user_id che la boat_id
         */

        $rel = BoatUser::create([
            'boat_id' => $boat['id'],
            'user_id' => $user_id,
            'profession_id' => $owner->id
        ]);

        $data = [
            "type" => "boats",
            "attributes" => [
                'owner_id' => $rel->id
            ]];

        $resp = Response(["data" => $data], 201);
        $resp->header('Content-Type', 'application/vnd.api+json');

        return $resp;

    }

    /**
     * @param Request $request
     * @param $record
     *
     * @return mixed
     */
    public function closedProjects(Request $request, $record)
    {
        $boat = Boat::findOrFail($record->id);
        return Utils::renderStandardJsonapiResponse($boat->closedProjectsJsonApi(), 200);
    }

    /**
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function archivedProjects(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user) {
                $boats = $user->boatsOfMyClosedProjects();
                return Utils::renderStandardJsonapiResponse($boats, 200);
            }
        }
    }

    /**
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function dashboard(Request $request)
    {
        $data = ['data' => []];
        if (Auth::check()) {
            $user = Auth::user();
            if ($user) {
                if ($request->input('active-projects') == 'on') {
                    $boats = $user->boatsOfMyActiveProjects();
                } else {
                    $boats = $user->boatsOfMyProjects();
                }
                if ($boats->count()) {
                    foreach ($boats as $boat) {
                        $owner = $boat->getOwner();
                        $attributes = $boat;
                        $attributes->owner = $owner;
                        $attributes->projects = $boat->projectsRelatedToUser($user->id);
                        $data['data'][] = [
                            'id' => $boat->id,
                            'type' => 'boats',
                            'attributes' => $attributes
                        ];
                    }
                }
            }
        }
        return Utils::renderStandardJsonapiResponse($data, 200);
    }
}
