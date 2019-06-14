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
//        $result = User::latest()->paginate();
//        return view('user.index', compact('result'));

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
//        $roles = Role::pluck('name', 'id');
//        return view('user.new', compact('roles'));

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
//        $this->validate($request, [
//            'name' => 'bail|required|min:2',
//            'email' => 'required|email|unique:users',
//            'password' => 'required|min:6',
//            'roles' => 'required|min:1'
//        ]);
//
//        // hash password
//        $request->merge(['password' => bcrypt($request->get('password'))]);
//
//        // Create the user
//        if ($user = User::create($request->except('roles', 'permissions'))) {
//            $this->syncPermissions($request, $user);
//            flash('User has been created.');
//        } else {
//            flash()->error('Unable to create user.');
//        }
//
//        return redirect()->route('users.index');


        //Validate name, email and password fields
        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6|confirmed',
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
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
//        $user = User::find($id);
//        $roles = Role::pluck('name', 'id');
//        $permissions = Permission::all('name', 'id');
//
//        return view('user.edit', compact('user', 'roles', 'permissions'));


        $user = User::findOrFail($id); //Get user with specified id
        $roles = Role::get(); //Get all roles
        $permissions = Permission::all('name', 'id');

        return view('user.edit', compact('user', 'roles', 'permissions'));
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
//        $this->validate($request, [
//            'name' => 'bail|required|min:2',
//            'email' => 'required|email|unique:users,email,' . $id,
//            'roles' => 'required|min:1'
//        ]);
//
//        // Get the user
//        $user = User::findOrFail($id);
//
//        // Update user
//        $user->fill($request->except('roles', 'permissions', 'password'));
//
//        // check for password change
//        if ($request->get('password')) {
//            $user->password = bcrypt($request->get('password'));
//        }
//
//        // Handle the user roles
//        $this->syncPermissions($request, $user);
//
//        $user->save();
//        flash()->success('User has been updated.');
//        return redirect()->route('users.index');


        $user = User::findOrFail($id);

        //Validate name, email and password fields
        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:users,email,'.$id,
            'password'=>'required|min:6|confirmed'
        ]);

        $input = $request->only(['name', 'email', 'password']); //Retreive the name, email and password fields
        $roles = $request['roles']; //Retreive all roles
        $user->fill($input)->save();

        if (isset($roles)) {
            $user->roles()->sync($roles);  //If one or more role is selected associate user to roles
        }
        else {
            $user->roles()->detach(); //If no role is selected remove exisiting role associated to a user
        }
        return redirect()->route('users.index')
            ->with('flash_message',
                'User successfully edited.');
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
}
