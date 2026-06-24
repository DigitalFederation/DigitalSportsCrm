<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfilePhotoExists
{
    /**
     * Routes that should be excluded from the profile photo check.
     */
    protected array $excludedRoutes = [
        'profile.complete-photo',
        'logout',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'secure-media.*',
        'livewire.*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated
        if (! $user) {
            return $next($request);
        }

        // Skip if route is excluded
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Skip if user doesn't have an individual profile (e.g., admin users)
        $individual = $user->individual;
        if (! $individual) {
            return $next($request);
        }

        // Check if individual has a profile photo
        if (! $individual->hasProfileImage()) {
            return redirect()->route('profile.complete-photo');
        }

        return $next($request);
    }

    /**
     * Check if the current route should be excluded from the profile photo check.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        $currentRoute = $request->route()?->getName();

        if (! $currentRoute) {
            return false;
        }

        foreach ($this->excludedRoutes as $pattern) {
            if (str_contains($pattern, '*')) {
                $regex = str_replace('*', '.*', $pattern);
                if (preg_match('/^' . $regex . '$/', $currentRoute)) {
                    return true;
                }
            } elseif ($currentRoute === $pattern) {
                return true;
            }
        }

        return false;
    }
}
