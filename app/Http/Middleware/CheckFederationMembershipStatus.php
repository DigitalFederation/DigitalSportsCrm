<?php

namespace App\Http\Middleware;

use Closure;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Http\Request;

class CheckFederationMembershipStatus
{
    public function handle(Request $request, Closure $next)
    {
        $federation = auth()->user()->federations()->first();

        // Skip if is_local federation
        if ($federation->is_local) {
            return $next($request);
        }

        // Skip check for payment-related routes
        if ($request->routeIs('federation.document.*')) {
            return $next($request);
        }

        $activeMembership = $federation->memberships()
            ->where('status_class', ActiveMembershipState::class)
            ->exists();

        if (! $activeMembership) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => __('The Annual Membership of your National Federation is not Active, please proceed to Payments'),
                ], 403);
            }

            return redirect()->route('federation.document.index')
                ->with('error', __('The Annual Membership of your National Federation is not Active, please proceed to Payments'));
        }

        return $next($request);
    }
}
