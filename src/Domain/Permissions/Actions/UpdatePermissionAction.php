<?php

namespace Domain\Permissions\Actions;

use App\Models\Permission;
use Domain\Permissions\Exceptions\SystemPermissionException;
use Illuminate\Support\Facades\DB;

class UpdatePermissionAction
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

    public static function execute(Permission $permission, array $data): Permission
    {
        // Validate system permissions
        if (in_array($permission->name, self::$systemPermissions)) {
            if (isset($data['name']) && $data['name'] !== $permission->name) {
                throw new SystemPermissionException('Cannot rename system permissions');
            }
        }

        return DB::transaction(function () use ($permission, $data) {
            $permission->update([
                'name' => $data['name'] ?? $permission->name,
                'description' => $data['description'] ?? $permission->description,
                'category' => $data['category'] ?? $permission->category,
                'updated_by' => auth()->id(),
            ]);

            // Clear permission cache
            app()['cache']->forget('spatie.permission.cache');

            return $permission;
        });
    }
}
