<?php

namespace Domain\Permissions\Actions;

use App\Models\RoutePermission;
use Illuminate\Support\Facades\DB;

class QuickAssignPermissionAction
{
    public static function execute(string $routeName, string $permissionName, bool $isActive = true): array
    {
        return DB::transaction(function () use ($routeName, $permissionName, $isActive) {
            // Check if route permission already exists
            $exists = RoutePermission::where('route_pattern', $routeName)->exists();

            // Assign the permission
            $routePermission = AssignPermissionToRouteAction::execute(
                $routeName,
                $permissionName,
                $isActive
            );

            return [
                'success' => true,
                'action' => $exists ? 'updated' : 'created',
                'route_name' => $routeName,
                'permission' => $permissionName,
                'is_active' => $isActive,
                'route_permission' => $routePermission,
            ];
        });
    }
}
