<?php

namespace App\Http\Controllers;

use function abort_unless;
use App\Http\Requests\RequestPhone;
use App\Http\Requests\RequestProjectUser;
use App\Http\Requests\RequestSite;
use App\Permission;
use App\Phone;
use App\ProjectUser;
use App\Role;
use App\User;
use App\UsersTel;
use Auth;
use const FLASH_ERROR;
use const FLASH_WARNING;
use Illuminate\Http\Request;
use Session;
use StormUtils;

class ProjectUserController extends Controller
{
    public function __construct()
    {
//        $this->middleware(['auth', 'isAdmin']); //isAdmin middleware lets only users with a //specific permission permission to access these resources
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        abort_unless($request->has('user_id'), 404);
//        $proj_users = ProjectUser::where('user_id', $request->user_id)->get();
        $proj_users = ProjectUser::where('user_id', $request->user_id)->paginate(StormUtils::getItemsPerPage());

        $user = User::findOrFail($request->user_id); //Get user with specified id

        return view('project_user.index')->with(['project_users' => $proj_users, 'user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort_unless($request->has('user_id'), 404);
        $user = User::findOrFail($request->user_id); //Get user with specified id

        return view('project_user.create', ['user' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RequestProjectUser  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestProjectUser $request)
    {
        $validated = $request->validated();
        $project_user = ProjectUser::create($validated);

        return redirect()->route('project_user.index', ['user_id' => $project_user->user->id])
            ->with(FLASH_SUCCESS, __('Project :projname related, with profession :profname', [
                'profname' => $project_user->profession->name,
                'projname' => $project_user->project->name,
            ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect('users');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */

    // **: vedi ticket: https://net7.codebasehq.com/projects/storm/tickets/155
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project_user = ProjectUser::findOrFail($id);
        if ($project_user->delete()) {
            $message = __('Project-User Relation has been deleted');
            $message_type = FLASH_SUCCESS;
        } else {
            $message = __('Project-User Relation not deleted');
            $message_type = FLASH_ERROR;
        }

        return redirect()->route('project_user.index', ['user_id' => $project_user->user->id])
            ->with($message_type, $message);
    }

    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmDestroy($id)
    {
        $project_user = ProjectUser::findOrFail($id);

        return view('project_user.delete')->with('project_user', $project_user);
    }
}
