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
        if (!User::onlyOne()) {
            if (!Auth::user()->hasPermissionTo(\Config::get('permissions.admin_roles_permissions'))) // If user does //not have this permission
            {
                abort('401');
            }
        }

        return $next($request);
    }
}
