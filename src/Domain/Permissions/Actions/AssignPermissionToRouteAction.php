<?php

namespace Domain\Permissions\Actions;

use App\Models\RoutePermission;
use Illuminate\Support\Facades\DB;

class AssignPermissionToRouteAction
{
    public static function execute(string $routePattern, string $permissionName, bool $isActive = true): RoutePermission
    {
        return DB::transaction(function () use ($routePattern, $permissionName, $isActive) {
            $routePermission = RoutePermission::updateOrCreate(
                ['route_pattern' => $routePattern],
                [
                    'permission_name' => $permissionName,
                    'is_active' => $isActive,
                    'updated_by' => auth()->id(),
                ]
            );

            // Clear route permission cache
            \App\Http\Middleware\CheckRoutePermission::clearRouteCache($routePattern);

            return $routePermission;
        });
    }
}
