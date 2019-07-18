<?php

namespace App\Policies;

use App\User;
use App\Boat ;
use App\BoatUser;

use Illuminate\Auth\Access\HandlesAuthorization;
use PhpParser\Node\Stmt\TryCatch;

class BoatPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any boats.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the boat.
     *
     * @param  \App\User  $user
     * @param  \App\Boat  $boat
     * @return mixed
     */
    public function view(User $user, Boat $boat)
    {

   //     var_dump($users);

        if ($user->can('Admin')) {
            return true;
        }

        if ($user->can('Boat manager')) {
            return true;
        }

          $c =  \App\Boat::find($boat->id)->associatedUsers->where('user_id', $user->id)->count();
        if ($c > 0) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can create boats.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can('Admin')) {
            return true;
        }

        if ($user->can('Boat manager')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the boat.
     *
     * @param  \App\User  $user
     * @param  \App\Boat  $boat
     * @return mixed
     */
    public function update(User $user, Boat $boat)
    {
        if ($user->can('Admin')) {
        return true;
    }

    if ($user->can('Boat manager')) {
        return true;
    }

        return false;
    }

    /**
     * Determine whether the user can delete the boat.
     *
     * @param  \App\User  $user
     * @param  \App\Boat  $boat
     * @return mixed
     */
    public function delete(User $user, Boat $boat)
    {
        if ($user->can('Admin')) {
            return true;
        }

        if ($user->can('Boat manager')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the boat.
     *
     * @param  \App\User  $user
     * @param  \App\Boat  $boat
     * @return mixed
     */
    public function restore(User $user, Boat $boat)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the boat.
     *
     * @param  \App\User  $user
     * @param  \App\Boat  $boat
     * @return mixed
     */
    public function forceDelete(User $user, Boat $boat)
    {
        if ($user->can('Admin')) {
            return true;
        }

        if ($user->can('Boat manager')) {
            return true;
        }

        return false;
    }
}
