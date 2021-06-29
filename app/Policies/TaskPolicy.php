<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use const PERMISSION_ADMIN;
use const PERMISSION_BACKEND_MANAGER;
use const PERMISSION_BOAT_MANAGER;
use const PERMISSION_WORKER;
use PhpParser\Node\Stmt\TryCatch;
use const ROLE_WORKER;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any boats.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the boat.
     *
     * @param  \App\Models\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function view(User $user, Boat $boat)
    {
        return true;
    }

    /**
     * Determine whether the user can create boats.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the boat.
     *
     * @param  \App\Models\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function update(User $user, Boat $boat)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the boat.
     *
     * @param  \App\Models\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function delete(User $user, Boat $boat)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the boat.
     *
     * @param  \App\Models\User $user
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
     * @param  \App\Models\User $user
     * @param  \App\Boat $boat
     * @return mixed
     */
    public function forceDelete(User $user, Boat $boat)
    {
        return true;
    }
}
