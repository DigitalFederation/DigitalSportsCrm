<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class CustomValidateSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (! URL::hasValidSignature($request)) {
            return redirect(route('verification.notice'))->with('error', 'The verification link is invalid probably because it has expired. Please request a new one clicking on the form button bellow.');
        }

        return $next($request);

    }
}
