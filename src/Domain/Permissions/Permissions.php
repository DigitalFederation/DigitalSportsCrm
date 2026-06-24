<?php

namespace Domain\Permissions;

use Domain\Permissions\Services\PermissionService;
use Domain\Permissions\Services\RoutePermissionService;

/**
 * The Permissions domain provides functionality for managing application permissions
 * and their assignments to routes.
 *
 * Key features:
 * - Dynamic permission creation and management
 * - Route-permission mapping
 * - Bulk operations and CSV import/export
 * - System permission protection
 * - Permission suggestions based on route patterns
 */
class Permissions
{
    /**
     * Get the permission service instance
     */
    public static function permissionService(): PermissionService
    {
        return app(PermissionService::class);
    }

    /**
     * Get the route permission service instance
     */
    public static function routePermissionService(): RoutePermissionService
    {
        return app(RoutePermissionService::class);
    }

    /**
     * System permissions that cannot be deleted or renamed
     */
    public const SYSTEM_PERMISSIONS = [
        'access users',
        'manage roles',
        'manage permissions',
        'manage role permissions',
        'manage protected roles',
        'access role management dashboard',
        'manage user roles',
    ];
}
