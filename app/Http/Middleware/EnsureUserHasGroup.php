<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasGroup
{
    public function handle(Request $request, Closure $next, ...$groups)
    {

        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {

            return $this->handleUnauthenticated($request);
        }

        // Get user group directly from database (no cache)
        $user->load('group');
        $userGroupCode = $user->group?->code;

        // Check if user belongs to any of the required groups
        // Support both single group (backward compatible) and multiple groups
        $allowedGroups = is_array($groups) && count($groups) === 1 && str_contains($groups[0], '|')
            ? explode('|', $groups[0])
            : $groups;

        if (in_array($userGroupCode, $allowedGroups)) {

            return $next($request);
        }

        return $this->handleUnauthorized($request);
    }

    protected function handleUnauthenticated($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return redirect()->route('login');
    }

    protected function handleUnauthorized($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        abort(403, 'You do not have permission to access this resource.');
    }
}
