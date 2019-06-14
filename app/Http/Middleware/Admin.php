<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (User::all()->count() != 1) {
            if (!Auth::user()->hasPermissionTo('Administer roles & permissions')) // If user does //not have this permission
            {
                abort('401');
            }
        }

        return $next($request);
    }
}
