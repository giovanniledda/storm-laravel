<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;
use function redirect;
use const ROLE_ADMIN;

class LogoutIfBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->can_login)
        {
            Auth::logout();
            abort('401');
        }
        return $next($request);
    }
}
