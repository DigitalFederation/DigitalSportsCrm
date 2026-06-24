<?php

namespace App\Observers;

use App\Http\Middleware\CheckRoutePermission;
use App\Models\RoutePermission;
use Illuminate\Support\Facades\Cache;

class RoutePermissionObserver
{
    /**
     * Handle the RoutePermission "created" event.
     */
    public function created(RoutePermission $routePermission): void
    {
        $this->clearCache($routePermission);
    }

    /**
     * Handle the RoutePermission "updated" event.
     */
    public function updated(RoutePermission $routePermission): void
    {
        $this->clearCache($routePermission);

        // Also clear cache for the old route pattern if it changed
        if ($routePermission->isDirty('route_pattern')) {
            $oldRoutePattern = $routePermission->getOriginal('route_pattern');
            Cache::forget("route_permission.{$oldRoutePattern}");
        }
    }

    /**
     * Handle the RoutePermission "deleted" event.
     */
    public function deleted(RoutePermission $routePermission): void
    {
        $this->clearCache($routePermission);
    }

    /**
     * Handle the RoutePermission "restored" event.
     */
    public function restored(RoutePermission $routePermission): void
    {
        $this->clearCache($routePermission);
    }

    /**
     * Handle the RoutePermission "force deleted" event.
     */
    public function forceDeleted(RoutePermission $routePermission): void
    {
        $this->clearCache($routePermission);
    }

    /**
     * Clear the cache for a route permission
     */
    protected function clearCache(RoutePermission $routePermission): void
    {
        // Clear specific route cache
        CheckRoutePermission::clearRouteCache($routePermission->route_pattern);

        // Clear all route permission caches
        // Note: Not all cache drivers support tags, so we'll use a different approach
        Cache::forget('route_permissions.all');
    }
}
