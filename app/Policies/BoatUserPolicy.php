<?php

namespace App\Policies;

use App\User;
use App\BoatUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoatUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any boat users.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the boat user.
     *
     * @param  \App\User  $user
     * @param  \App\BoatUser  $boatUser
     * @return mixed
     */
    public function view(User $user, BoatUser $boatUser)
    {
        //
    }

    /**
     * Determine whether the user can create boat users.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {

         if ($user->can('admin') || $user->can('bootmanager')) {
             return true;
         }
         return false;
    }

    /**
     * Determine whether the user can update the boat user.
     *
     * @param  \App\User  $user
     * @param  \App\BoatUser  $boatUser
     * @return mixed
     */
    public function update(User $user, BoatUser $boatUser)
    {
        //
    }

    /**
     * Determine whether the user can delete the boat user.
     *
     * @param  \App\User  $user
     * @param  \App\BoatUser  $boatUser
     * @return mixed
     */
    public function delete(User $user, BoatUser $boatUser)
    {
        //
    }

    /**
     * Determine whether the user can restore the boat user.
     *
     * @param  \App\User  $user
     * @param  \App\BoatUser  $boatUser
     * @return mixed
     */
    public function restore(User $user, BoatUser $boatUser)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the boat user.
     *
     * @param  \App\User  $user
     * @param  \App\BoatUser  $boatUser
     * @return mixed
     */
    public function forceDelete(User $user, BoatUser $boatUser)
    {
        //
    }
}
