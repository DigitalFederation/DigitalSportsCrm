<?php

namespace Domain\Permissions\Actions;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Permission;
use Domain\Permissions\Exceptions\PermissionException;
use Illuminate\Support\Collection;

class AssignPermissionToMenuItemAction
{
    /**
     * Assign permissions to a menu item
     *
     * @param  array|Collection  $permissions  Permission names or Permission models
     * @param  bool  $replace  Whether to replace existing permissions or merge
     *
     * @throws PermissionException
     */
    public static function execute(
        MenuItem $menuItem,
        array|Collection $permissions,
        bool $replace = false
    ): MenuItem {
        // Convert to collection if array
        $permissions = collect($permissions);

        // Validate and normalize permissions
        $permissionNames = $permissions->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->name;
            }

            if (is_string($permission)) {
                // Validate permission exists
                if (! Permission::where('name', $permission)->exists()) {
                    throw new PermissionException("Permission '{$permission}' does not exist");
                }

                return $permission;
            }

            throw new PermissionException('Invalid permission type provided');
        })->unique()->values()->toArray();

        // Get existing permissions if not replacing
        if (! $replace) {
            $existingPermissions = $menuItem->permissions ?? [];
            $permissionNames = array_unique(array_merge($existingPermissions, $permissionNames));
        }

        // Update menu item permissions
        $menuItem->permissions = $permissionNames;
        $menuItem->save();

        // Clear menu cache
        if ($menuItem->menu) {
            $menuItem->menu->clearCache();
        }

        return $menuItem->fresh();
    }

    /**
     * Remove permissions from a menu item
     *
     * @param  array|Collection  $permissions  Permission names to remove
     */
    public static function removePermissions(
        MenuItem $menuItem,
        array|Collection $permissions
    ): MenuItem {
        $permissions = collect($permissions);
        $currentPermissions = collect($menuItem->permissions ?? []);

        // Remove specified permissions
        $updatedPermissions = $currentPermissions->diff($permissions)->values()->toArray();

        $menuItem->permissions = $updatedPermissions;
        $menuItem->save();

        // Clear menu cache
        if ($menuItem->menu) {
            $menuItem->menu->clearCache();
        }

        return $menuItem->fresh();
    }

    /**
     * Clear all permissions from a menu item
     */
    public static function clearPermissions(MenuItem $menuItem): MenuItem
    {
        $menuItem->permissions = [];
        $menuItem->save();

        // Clear menu cache
        if ($menuItem->menu) {
            $menuItem->menu->clearCache();
        }

        return $menuItem->fresh();
    }

    /**
     * Sync permissions with menu items across the system
     * Useful when permission names change
     *
     * @return int Number of menu items updated
     */
    public static function syncPermissionName(
        string $oldPermissionName,
        string $newPermissionName
    ): int {
        $updatedCount = 0;

        MenuItem::whereJsonContains('permissions', $oldPermissionName)
            ->each(function (MenuItem $menuItem) use ($oldPermissionName, $newPermissionName, &$updatedCount) {
                $permissions = collect($menuItem->permissions);

                // Replace old permission with new
                $permissions = $permissions->map(function ($permission) use ($oldPermissionName, $newPermissionName) {
                    return $permission === $oldPermissionName ? $newPermissionName : $permission;
                })->unique()->values()->toArray();

                $menuItem->permissions = $permissions;
                $menuItem->save();

                $updatedCount++;
            });

        // Clear all menu caches
        Menu::all()->each->clearCache();

        return $updatedCount;
    }
}
