<?php

namespace Domain\Permissions\Services;

use App\Models\RoutePermission;
use Domain\Permissions\Actions\AssignPermissionToRouteAction;
use Domain\Permissions\Actions\BulkAssignPermissionsToRoutesAction;
use Domain\Permissions\Actions\ScanRoutesAction;
use Domain\Permissions\Actions\SuggestPermissionsForRouteAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class RoutePermissionService
{
    /**
     * Get all route permissions with optional filters
     */
    public function getRoutePermissions(array $filters = []): Collection
    {
        $query = RoutePermission::query();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('route_pattern', 'like', "%{$filters['search']}%")
                    ->orWhere('permission_name', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('route_pattern')->get();
    }

    /**
     * Scan all application routes
     */
    public function scanRoutes(array $filters = []): SupportCollection
    {
        return ScanRoutesAction::execute($filters);
    }

    /**
     * Get route with its current permission mapping
     */
    public function getRoutesWithPermissions(array $filters = []): SupportCollection
    {
        $routes = $this->scanRoutes($filters);
        $routePermissions = RoutePermission::pluck('permission_name', 'route_pattern');

        return $routes->map(function ($route) use ($routePermissions) {
            $route['current_permission'] = $routePermissions[$route['name']] ?? null;
            $route['has_permission'] = isset($routePermissions[$route['name']]);

            return $route;
        });
    }

    /**
     * Suggest permissions for a route
     */
    public function suggestPermissions(string $routeName): array
    {
        return SuggestPermissionsForRouteAction::execute($routeName);
    }

    /**
     * Assign permission to a route
     */
    public function assignPermissionToRoute(string $routePattern, string $permissionName, bool $isActive = true): RoutePermission
    {
        return AssignPermissionToRouteAction::execute($routePattern, $permissionName, $isActive);
    }

    /**
     * Bulk assign permissions to routes
     */
    public function bulkAssignPermissions(array $assignments): array
    {
        return BulkAssignPermissionsToRoutesAction::execute($assignments);
    }

    /**
     * Remove permission from route
     */
    public function removePermissionFromRoute(string $routePattern): bool
    {
        $routePermission = RoutePermission::where('route_pattern', $routePattern)->first();

        if ($routePermission) {
            // Clear cache before deletion
            \App\Http\Middleware\CheckRoutePermission::clearRouteCache($routePattern);

            return $routePermission->delete();
        }

        return false;
    }

    /**
     * Toggle route permission status
     */
    public function toggleRoutePermissionStatus(RoutePermission $routePermission): RoutePermission
    {
        $routePermission->update([
            'is_active' => ! $routePermission->is_active,
            'updated_by' => auth()->id(),
        ]);

        return $routePermission;
    }
}
