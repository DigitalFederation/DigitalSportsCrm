<?php

namespace App\Http\Middleware;

use App\Models\RoutePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the current route name
        $routeName = $request->route()->getName();

        // If no route name, allow the request (unnamed routes)
        if (! $routeName) {
            return $next($request);
        }

        // Check if user is authenticated
        if (! auth()->check()) {
            abort(401, 'Unauthenticated');
        }

        // Check cached permissions first
        $cacheKey = "route_permission.{$routeName}";
        $requiredPermission = Cache::remember($cacheKey, 3600, function () use ($routeName) {
            $routePermission = RoutePermission::where('route_pattern', $routeName)
                ->where('is_active', true)
                ->first();

            return $routePermission ? $routePermission->permission_name : null;
        });

        // If no permission is required for this route, allow access
        if (! $requiredPermission) {
            return $next($request);
        }

        // Check if user has the required permission
        if (auth()->user()->can($requiredPermission)) {
            return $next($request);
        }

        // User doesn't have permission
        abort(403, 'Unauthorized. Required permission: ' . $requiredPermission);
    }

    /**
     * Clear route permission cache
     */
    public static function clearCache(): void
    {
        // Clear all route permission cache entries
        Cache::flush(); // In production, you might want to be more selective
    }

    /**
     * Clear cache for a specific route
     */
    public static function clearRouteCache(string $routeName): void
    {
        Cache::forget("route_permission.{$routeName}");
    }
}
