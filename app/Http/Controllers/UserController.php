<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestAddress;
use App\Http\Requests\RequestPhone;
use App\Phone;
use App\UsersTel;
use Auth;
use const FLASH_ERROR;
use const FLASH_WARNING;
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
        $user->is_storm = $request->has('is_storm');
        $user->disable_login = $request->has('disable_login');
        $user->save();

        $roles = $request['roles']; // Retrieving the roles field
        //Checking if a role was selected
        if (isset($roles)) {
            foreach ($roles as $role) {
                $role_r = Role::where('id', '=', $role)->firstOrFail();
                $user->assignRole($role_r); // Assigning role to user
            }
        }

        return redirect()->route('users.index')
            ->with(FLASH_SUCCESS, __('User successfully added.'));
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

    // **: vedi ticket: https://net7.codebasehq.com/projects/storm/tickets/155
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        //Validate name, email and password fields
        $validated = $this->validate($request, [
            'name' => 'required|max:120',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed' // **
        ]);

        // **
        $fields = !empty($validated['password']) ? ['name', 'email', 'password'] : ['name', 'email'];

        $input = $request->only($fields); //Retreive the name, email and password fields
        $user->fill($input);
        $user->is_storm = $request->has('is_storm');
        $user->disable_login = $request->has('disable_login');
        $user->save();

        $roles = $request['roles']; //Retreive all roles
        if (isset($roles)) {
            $user->roles()->sync($roles);  //If one or more role is selected associate user to roles
        } else {
            $user->roles()->detach(); //If no role is selected remove exisiting role associated to a user
        }
        return redirect()->route('users.index')
            ->with(FLASH_SUCCESS, __('User successfully edited.'));
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
            flash()->warning(__('Deletion of currently logged in user is not allowed :('))->important();
            return redirect()->back();
        }

        if (User::findOrFail($id)->delete()) {
            flash()->success(__('User has been deleted'));
        } else {
            flash()->success(__('User not deleted'));
        }

        return redirect()->route('users.index');
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
     * @param  \App\Http\Requests\RequestPhone  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function phonesStore(RequestPhone $request, $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);

        try {
            $phone = Phone::create($validated); // si porta dietro user_id
            $message = __('New phone added for user :name!', ['name' => $user->name]);
            $message_type = FLASH_SUCCESS;
        } catch (\Exception $e) {
            $message = __('Something went wrong adding new phone, check your data! [:msg]', ['msg' => $e->getMessage()]);
            $message_type = FLASH_ERROR;
        }

        return redirect()->route('users.phones.index', ['id' => $id])->with($message_type, $message);
    }


    /**
     * Remove the specified phone from storage.
     *
     * @param  int $user_id
     * @param  int $phone_id
     * @return \Illuminate\Http\Response
     */
    public function phonesDestroy($user_id, $phone_id)
    {
//        $user = User::findOrFail($user_id);
        $phone = UsersTel::findOrFail($phone_id);
        $phone_num = $phone->phone_number;
        $message = __('Phone [#:id - :num] has not been deleted!', ['id' => $phone_id, 'num' => $phone_num]);
        $message_type = FLASH_ERROR;

        if ($phone) {
            try {
                $phone->delete();
                $message = __('Phone [#:id - :num] deleted!', ['id' => $phone_id, 'num' => $phone_num]);
                $message_type = FLASH_SUCCESS;
            } catch (\Exception $e) {
                $message = __('Phone [#:id - :num] has not been deleted!', ['id' => $phone_id, 'num' => $phone_num]);
                $message_type = FLASH_ERROR;
            }
        }

        return redirect()->route('users.phones.index', ['id' => $user_id])->with($message_type, $message);
    }


    /**
     * Ask confirmation about the specified phone from storage to remove.
     *
     * @param  int $user_id
     * @param  int $phone_id
     * @return \Illuminate\Http\Response
     */
    public function phonesConfirmDestroy($user_id, $phone_id)
    {
        $user = User::findOrFail($user_id);
        $phone = UsersTel::findOrFail($phone_id);

        return view('users.phones.delete')->with(['phone' => $phone, 'user' => $user]);
    }


    /*
     * *************************************************************
     *                      ADDRESSES
     * *************************************************************
     */

    /**
     * Addresses list for a User
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function addressesIndex($id)
    {
        $user = User::findOrFail($id);
        $addresses = $user->getAddresses();
        return view('users.addresses.index')->with(['addresses' => $addresses, 'user' => $user]);
    }

    /**
     * Show the form for creating a new addresses for the User.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function addressesCreate($id)
    {
        return view('users.addresses.create')->with(['user' => User::findOrFail($id)]);
    }


    /**
     * Store a newly created addresses for the User in storage.
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
            // Qua si innesca anche il Validator della HasAddresses che segue queste regole:
//            'street'       => 'required|string|min:3|max:60',
//            'street_extra' => 'string|min:3|max:60',
//            'city'         => 'required|string|min:3|max:60',
//            'state'        => 'string|min:3|max:60',
//            'post_code'    => 'required|min:4|max:10|AlphaDash',
//            'country_id'   => 'required|integer',
            // ...la country viene gestite ricercando al stringa nei campi iso_3166_2 o iso_3166_3 di countries
            $user->addAddress($validated);
            $message = __('New address added for user :name!', ['name' => $user->name]);
            $message_type = FLASH_SUCCESS;

        } catch (\Exception $e) {
            $message = __('Something went wrong adding new address, check your data!');
            $message_type = FLASH_ERROR;
        }

        return redirect()->route('users.addresses.index', ['id' => $id])->with($message_type, $message);
    }


    /**
     * Show the form for editing the specified addresses for the User.
     *
     * @param  int $user_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesEdit($user_id, $address_id)
    {
        $user = User::findOrFail($user_id);
        $address = $user->getAddress($address_id);

        return view('users.addresses.edit')->with(['address' => $address, 'user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\RequestAddress  $request
     * @param  int $user_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesUpdate(RequestAddress $request, $user_id, $address_id)
    {
        $message = __('Address [:id] has not been updated!', ['id' => $address_id]);
        $message_type = FLASH_ERROR;
        $validated = $request->validated();
        $user = User::findOrFail($user_id);

        $address = $user->getAddress($address_id);
        if ($address) {
            try {
                $user->updateAddress($address, $validated);
                $message = __('Address [:id] in :city updated!', ['id' => $address_id, 'city' => $address->city]);
                $message_type = FLASH_SUCCESS;

            } catch (\Exception $e) {
                $message = __('Something went wrong updating address [:id], check your data!', ['id' => $address_id]);
                $message_type = FLASH_ERROR;
            }
        }

        return redirect()->route('users.addresses.index', ['id' => $user_id])->with($message_type, $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $user_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesDestroy($user_id, $address_id)
    {
        $message = __('Address [:id] has not been deleted!', ['id' => $address_id]);
        $message_type = FLASH_ERROR;
        $user = User::findOrFail($user_id);

        $address = $user->getAddress($address_id);
        if ($address) {
            $user->deleteAddress($address); // delete by passing it as argument
            $message = __('Address [:id] deleted!', ['id' => $address_id]);
            $message_type = FLASH_SUCCESS;
        }

        return redirect()->route('users.addresses.index', ['id' => $user_id])->with($message_type, $message);
    }


    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $user_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesConfirmDestroy($user_id, $address_id)
    {
        $user = User::findOrFail($user_id);
        $address = $user->getAddress($address_id);

        return view('users.addresses.delete')->with(['address' => $address, 'user' => $user]);
    }
}
