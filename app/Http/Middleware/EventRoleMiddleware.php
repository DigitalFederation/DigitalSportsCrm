<?php

namespace App\Http\Middleware;

use Closure;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $role  The required role (technical_delegate, chief_judge, competition_director)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Get the event from the route
        $event = $request->route('event');

        if (! $event instanceof Event) {
            abort(404, 'Event not found');
        }

        // Get the authenticated user
        $user = Auth::user();

        if (! $user || ! $user->individual) {
            abort(403, 'Unauthorized: No individual profile found');
        }

        // Check if the user's individual has the specified role(s) for this event
        $roles = explode(',', $role);
        $hasRole = EventRole::where('event_id', $event->id)
            ->where('individual_id', $user->individual->id)
            ->whereIn('role', $roles)
            ->exists();

        if (! $hasRole) {
            abort(403, 'Unauthorized: You do not have the required role for this event');
        }

        return $next($request);
    }
}
