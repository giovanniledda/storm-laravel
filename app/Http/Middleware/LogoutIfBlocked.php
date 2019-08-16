<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use CloudCreativity\LaravelJsonApi\Utils\Helpers;

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
            if (!Helpers::wantsJsonApi($request)) {
                Auth::logout();  // via api non funziona, faccio logout solo via web per ora
            }
            abort('401');
        }
        return $next($request);
    }
}
