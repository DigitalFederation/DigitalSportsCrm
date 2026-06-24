<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecurityValidationService
{
    private const MINIMUM_SUPER_ADMIN_COUNT = 1;

    protected array $protectedSystemRoles = [
        'admin',
    ];

    protected array $protectedAdminRoles = [
        'federation-admin',
        'association-sport-admin',
        'association-scientific-admin',
        'association-admin',
        'association-territorial-admin',
    ];

    public function canDeleteRole(Role $role): bool
    {
        if ($this->isProtectedRole($role)) {
            return false;
        }

        if ($this->isLastSuperAdminRole($role)) {
            return false;
        }

        return true;
    }

    public function canModifyRole(Role $role, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->isSystemRole($role) && ! $this->userCanManageSystemRoles($user)) {
            return false;
        }

        if ($this->isAdminRole($role) && ! $this->userCanManageAdminRoles($user)) {
            return false;
        }

        return true;
    }

    public function canRemovePermissionFromRole(Role $role, string $permissionName): bool
    {
        if ($this->isSystemRole($role) && $this->isCriticalPermission($permissionName)) {
            return false;
        }

        return true;
    }

    public function validateRoleDeletion(Role $role): array
    {
        $issues = [];

        if ($this->isProtectedRole($role)) {
            $issues[] = __('role_management.errors.protected_role_cannot_be_deleted');
        }

        if ($this->isLastSuperAdminRole($role)) {
            $issues[] = __('role_management.errors.cannot_delete_last_super_admin');
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            $issues[] = __('role_management.errors.role_has_assigned_users', ['count' => $usersCount]);
        }

        return $issues;
    }

    public function getRoleDeletionImpact(Role $role): array
    {
        $usersAffected = $role->users()->count();
        $permissionsCount = $role->permissions()->count();
        $routesAffected = $this->getRoutesAffectedByRole($role);

        // Provide both canonical keys and view-friendly aliases for compatibility
        return [
            // Canonical keys
            'users_affected' => $usersAffected,
            'permissions_count' => $permissionsCount,
            'routes_affected' => $routesAffected,
            'dependencies' => $this->getRoleDependencies($role),

            // Aliases expected by existing Blade templates
            'has_users' => $usersAffected > 0,
            'user_count' => $usersAffected,
            'affected_permissions' => $permissionsCount,
        ];
    }

    public function validatePermissionRemoval(Role $role, array $permissions): array
    {
        $issues = [];

        if ($this->isSystemRole($role)) {
            $criticalPermissions = array_intersect($permissions, $this->getCriticalPermissions());
            if (! empty($criticalPermissions)) {
                $issues[] = __('role_management.errors.cannot_remove_critical_permissions', [
                    'permissions' => implode(', ', $criticalPermissions),
                ]);
            }
        }

        return $issues;
    }

    public function ensureMinimumAdminCount(): bool
    {
        $superAdminCount = User::role('admin')->count();

        return $superAdminCount >= self::MINIMUM_SUPER_ADMIN_COUNT;
    }

    public function isProtectedRole(Role $role): bool
    {
        return $role->is_protected || in_array($role->name, $this->getAllProtectedRoles());
    }

    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, $this->protectedSystemRoles) || $role->protection_level === 'system';
    }

    public function isAdminRole(Role $role): bool
    {
        return in_array($role->name, $this->protectedAdminRoles) || $role->protection_level === 'admin';
    }

    public function userCanManageSystemRoles(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || $user->can('manage protected roles');
    }

    public function userCanManageAdminRoles(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'association-sport-admin', 'association-scientific-admin', 'association-admin']) ||
            $user->can('manage roles');
    }

    public function canUserManageRole(Role $role, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->isSystemRole($role)) {
            return $this->userCanManageSystemRoles($user);
        }

        if ($this->isAdminRole($role)) {
            return $this->userCanManageAdminRoles($user);
        }

        return $user->can('manage roles');
    }

    protected function isLastSuperAdminRole(Role $role): bool
    {
        if ($role->name !== 'admin') {
            return false;
        }

        $superAdminCount = User::role('admin')->count();

        return $superAdminCount <= self::MINIMUM_SUPER_ADMIN_COUNT;
    }

    protected function isCriticalPermission(string $permissionName): bool
    {
        $criticalPermissions = $this->getCriticalPermissions();

        return in_array($permissionName, $criticalPermissions);
    }

    protected function getCriticalPermissions(): array
    {
        return [
            'manage roles',
            'manage role permissions',
            'manage protected roles',
            'manage user roles',
            'access users',
        ];
    }

    protected function getAllProtectedRoles(): array
    {
        return array_merge($this->protectedSystemRoles, $this->protectedAdminRoles);
    }

    protected function getRoutesAffectedByRole(Role $role): int
    {
        // Join order matters: first map route_permissions.permission_name -> permissions.name,
        // then link to role_has_permissions via permissions.id.
        return DB::table('route_permissions')
            ->join('permissions', 'route_permissions.permission_name', '=', 'permissions.name')
            ->join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $role->id)
            ->distinct('route_permissions.route_pattern')
            ->count();
    }

    protected function getRoleDependencies(Role $role): array
    {
        $dependencies = [];

        if ($role->permissions()->count() > 0) {
            $dependencies['permissions'] = $role->permissions()->pluck('name')->toArray();
        }

        $childRoles = Role::where('name', 'like', $role->name . '-%')->get();
        if ($childRoles->count() > 0) {
            $dependencies['child_roles'] = $childRoles->pluck('name')->toArray();
        }

        return $dependencies;
    }

    public function getProtectedRoleInfo(Role $role): array
    {
        return [
            'is_protected' => $this->isProtectedRole($role),
            'protection_level' => $role->protection_level ?? 'user',
            'can_delete' => $this->canDeleteRole($role),
            'can_modify' => $this->canModifyRole($role),
            'protection_reason' => $this->getProtectionReason($role),
        ];
    }

    protected function getProtectionReason(Role $role): ?string
    {
        if ($this->isLastSuperAdminRole($role)) {
            return __('role_management.protection.last_super_admin');
        }

        if ($this->isSystemRole($role)) {
            return __('role_management.protection.system_role');
        }

        if ($this->isAdminRole($role)) {
            return __('role_management.protection.admin_role');
        }

        if ($role->is_protected) {
            return __('role_management.protection.manually_protected');
        }

        return null;
    }
}
