<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {

        if (Auth::check() && ! Auth::user()->active) {

            Auth::guard('web')->logout();

            return redirect()->route('login')->with('error', 'Your account is not active.');
        }

        return $next($request);
    }
}
