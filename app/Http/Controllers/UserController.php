<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\User;
use App\Role;
use App\Permission;


class UserController extends Controller
{

    public function __construct()
    {
//        $this->middleware(['auth', 'isAdmin']); //isAdmin middleware lets only users with a //specific permission permission to access these resources
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get all users and pass it to the view
        $users = User::all();
        return view('users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Get all roles and pass it to the view
        $roles = Role::get();
        return view('users.create', ['roles' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //Validate name, email and password fields
        $this->validate($request, [
            'name' => 'required|max:120',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'roles' => 'required|min:1'
        ]);

        $user = User::create($request->only('email', 'name', 'password')); //Retrieving only the email and password data

        $roles = $request['roles']; // Retrieving the roles field
        //Checking if a role was selected
        if (isset($roles)) {
            foreach ($roles as $role) {
                $role_r = Role::where('id', '=', $role)->firstOrFail();
                $user->assignRole($role_r); // Assigning role to user
            }
        }

        return redirect()->route('users.index')
            ->with('flash_message', 'User successfully added.');
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
        $user = User::findOrFail($id); //Get user with specified id
        $roles = Role::get(); //Get all roles
        $permissions = Permission::all('name', 'id');

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        //Validate name, email and password fields
        $this->validate($request, [
            'name' => 'required|max:120',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'required|min:6|confirmed'
        ]);

        $input = $request->only(['name', 'email', 'password']); //Retreive the name, email and password fields
        $user->fill($input)->save();

        $roles = $request['roles']; //Retreive all roles
        if (isset($roles)) {
            $user->roles()->sync($roles);  //If one or more role is selected associate user to roles
        } else {
            $user->roles()->detach(); //If no role is selected remove exisiting role associated to a user
        }
        return redirect()->route('users.index')
            ->with('flash_message', 'User successfully edited.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->id == $id) {
            flash()->warning('Deletion of currently logged in user is not allowed :(')->important();
            return redirect()->back();
        }

        if (User::findOrFail($id)->delete()) {
            flash()->success('User has been deleted');
        } else {
            flash()->success('User not deleted');
        }

        return redirect()->back();
    }

    private function syncPermissions(Request $request, $user)
    {
        // Get the submitted roles
        $roles = $request->get('roles', []);
        $permissions = $request->get('permissions', []);

        // Get the roles
        $roles = Role::find($roles);

        // check for current role changes
        if (!$user->hasAllRoles($roles)) {
            // reset all direct permissions for user
            $user->permissions()->sync([]);
        } else {
            // handle permissions
            $user->syncPermissions($permissions);
        }

        $user->syncRoles($roles);
        return $user;
    }


    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmDestroy($id)
    {
        $user = User::findOrFail($id);
        return view('users.delete')->withUser($user);
    }


    /*
     * *************************************************************
     *                      TELEPHONES
     * *************************************************************
     */


    /**
     * Phones list for a user
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function phonesIndex($id)
    {
        $user = User::findOrFail($id);
        $phones = $user->phones;
        return view('users.phones.index')->with(['phones' => $phones, 'user' => $user]);
    }

    /**
     * Show the form for creating a new phones for the user.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function phonesCreate($id)
    {
        return view('users.phones.create')->with(['user' => User::findOrFail($id)]);
    }


    /**
     * Store a newly created addresses for the Site in storage.
     *
     * @param  \App\Http\Requests\RequestAddress  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function addressesStore(RequestAddress $request, $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);

        try {
//            $site->addAddress($validated);
            $message = __('New address added for site :name!', ['name' => $user->name]);
        } catch (\Exception $e) {
            $message = __('Something went wrong adding new address, check your data!');
        }

        return redirect()->route('sites.addresses.index', ['id' => $id])->with('flash_message', $message);
    }

}
