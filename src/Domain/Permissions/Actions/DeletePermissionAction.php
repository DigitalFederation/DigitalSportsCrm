<?php

namespace Domain\Permissions\Actions;

use App\Models\Permission;
use Domain\Permissions\Exceptions\SystemPermissionException;
use Illuminate\Support\Facades\DB;

class DeletePermissionAction
{
    protected static array $systemPermissions = [
        'access users',
        'manage roles',
        'manage permissions',
        'manage role permissions',
        'manage protected roles',
        'access role management dashboard',
        'manage user roles',
    ];

    public static function execute(Permission $permission): bool
    {
        // Prevent deletion of system permissions
        if (in_array($permission->name, self::$systemPermissions)) {
            throw new SystemPermissionException("Cannot delete system permission: {$permission->name}");
        }

        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            $roleNames = $permission->roles()->pluck('name')->join(', ');
            throw new SystemPermissionException("Cannot delete permission '{$permission->name}' as it is assigned to roles: {$roleNames}");
        }

        return DB::transaction(function () use ($permission) {
            $result = $permission->delete();

            // Clear permission cache
            app()['cache']->forget('spatie.permission.cache');

            return $result;
        });
    }
}
