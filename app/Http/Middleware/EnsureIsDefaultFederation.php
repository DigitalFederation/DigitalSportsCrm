<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsDefaultFederation
{
    public function handle(Request $request, Closure $next): mixed
    {
        $userFederations = auth()->user()->federations;

        if ($userFederations->contains(fn ($federation) => $federation->is_default_federation)) {
            return $next($request);
        }

        abort(403);
    }
}
