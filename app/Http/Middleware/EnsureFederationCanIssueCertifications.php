<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFederationCanIssueCertifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, __('federation.unauthenticated'));
        }

        $federation = $user->federations()->first();

        if (! $federation) {
            abort(403, __('federation.no_federation_associated'));
        }

        if (! $federation->canIssueCertifications()) {
            abort(403, __('federation.cannot_issue_certifications'));
        }

        return $next($request);
    }
}
