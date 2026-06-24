<?php

namespace App\Http\Middleware;

use App\Models\Committee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFederationCanManageCommittee
{
    /**
     * Handle an incoming request.
     *
     * Validates that the federation can manage the specified committee.
     * Can be used with a specific committee code parameter or will check
     * the committee from request filters/route parameters.
     *
     * Usage:
     * - middleware('federation.can_manage_committee') - checks committee from request
     * - middleware('federation.can_manage_committee:SPORT') - checks specific committee
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $committeeCode = null): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, __('federation.unauthenticated'));
        }

        $federation = $user->federations()->first();

        if (! $federation) {
            abort(403, __('federation.no_federation_associated'));
        }

        // If a specific committee code is provided, check that
        if ($committeeCode) {
            $committee = Committee::where('code', strtoupper($committeeCode))->first();
            if (! $committee || ! $federation->canManageCommittee($committee)) {
                abort(403, __('federation.cannot_manage_committee'));
            }

            return $next($request);
        }

        // If committee comes from request (filter or route parameter)
        $requestCommittee = $request->input('filter.committee')
            ?? $request->route('committee')
            ?? $request->input('committee')
            ?? $request->input('committee_code');

        if ($requestCommittee) {
            $committee = Committee::where('code', strtoupper($requestCommittee))->first();
            if ($committee && ! $federation->canManageCommittee($committee)) {
                abort(403, __('federation.cannot_manage_committee'));
            }
        }

        return $next($request);
    }
}
