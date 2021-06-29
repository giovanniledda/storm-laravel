<?php

namespace App\Policies;

use App\Boat;
use App\BoatUser;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use const PERMISSION_ADMIN;
use const PERMISSION_BOAT_MANAGER;
use PhpParser\Node\Stmt\TryCatch;

class BoatPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any boats.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the boat.
     *
     * @param  \App\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function view(User $user, Boat $boat)
    {
        $c = Boat::find($boat->id)->associatedUsers->where('user_id', $user->id)->count();
        if ($c > 0) {
            return true;
        }

        // ADMIN VEDE SEMPRE TUTTE LE BARCHE
        if ($user->can(PERMISSION_ADMIN)) {
            return true;
        }

        // se il ruolo e' worker devo controllare che l'utente sia in project_user
        if ($user->hasRole(ROLE_WORKER)) {
            //todo
            return true;
        }

        // se il ruolo e' boat_manager devo controllare che l'utente sia in boat_user
        if ($user->can(PERMISSION_BOAT_MANAGER)) {
            // todo
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create boats.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can(PERMISSION_ADMIN)) {
            return true;
        }

        if ($user->can(PERMISSION_WORKER)) {
            return true;
        }

        if ($user->can(PERMISSION_BACKEND_MANAGER)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the boat.
     *
     * @param  \App\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function update(User $user, Boat $boat)
    {
        if ($user->can(PERMISSION_ADMIN)) {
            return true;
        }

        if ($user->can(PERMISSION_BACKEND_MANAGER)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the boat.
     *
     * @param  \App\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function delete(User $user, Boat $boat)
    {
        if ($user->can(PERMISSION_ADMIN)) {
            return true;
        }

        if ($user->can(PERMISSION_BACKEND_MANAGER)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the boat.
     *
     * @param  \App\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function restore(User $user, Boat $boat)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the boat.
     *
     * @param  \App\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function forceDelete(User $user, Boat $boat)
    {
        if ($user->can(PERMISSION_ADMIN)) {
            return true;
        }

        if ($user->can(PERMISSION_ADMIN)) {
            return true;
        }

        return false;
    }
}
