<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use const ROLE_ADMIN;

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
        if (! User::onlyOne()) {
            if (! Auth::user()->hasRole(ROLE_ADMIN)) { // If user does //not have this permission
                abort('401');
            }
        }

        return $next($request);
    }
}
