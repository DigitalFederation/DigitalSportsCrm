<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleManagementService
{
    public function __construct(
        protected SecurityValidationService $securityService
    ) {}

    public function createRole(array $data): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'scope' => $data['scope'] ?? null,
                'is_protected' => $data['is_protected'] ?? false,
                'protection_level' => $data['protection_level'] ?? 'user',
                'created_by' => Auth::id(),
            ]);

            if (! empty($data['permissions'])) {
                $this->assignPermissionsToRole($role, $data['permissions']);
            }

            activity()
                ->performedOn($role)
                ->withProperties([
                    'role_data' => $data,
                    'permissions' => $data['permissions'] ?? [],
                ])
                ->log('Role created');

            DB::commit();

            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRole(Role $role, array $data): Role
    {
        if (! $this->securityService->canModifyRole($role)) {
            throw new \Exception(__('role_management.errors.cannot_modify_protected_role'));
        }

        DB::beginTransaction();

        try {
            $originalData = $role->toArray();

            $role->update([
                'name' => $data['name'] ?? $role->name,
                'description' => $data['description'] ?? $role->description,
                'category' => $data['category'] ?? $role->category,
                'scope' => $data['scope'] ?? $role->scope,
                'updated_by' => Auth::id(),
            ]);

            if (isset($data['permissions'])) {
                $this->syncRolePermissions($role, $data['permissions']);
            }

            activity()
                ->performedOn($role)
                ->withProperties([
                    'original_data' => $originalData,
                    'updated_data' => $data,
                    'permissions' => $data['permissions'] ?? null,
                ])
                ->log('Role updated');

            DB::commit();

            return $role->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRole(Role $role): bool
    {
        if (! $this->securityService->canDeleteRole($role)) {
            throw new \Exception(__('role_management.errors.cannot_delete_protected_role'));
        }

        $issues = $this->securityService->validateRoleDeletion($role);
        if (! empty($issues)) {
            throw new \Exception(implode(' ', $issues));
        }

        DB::beginTransaction();

        try {
            $roleData = $role->toArray();
            $permissions = $role->permissions->pluck('name')->toArray();
            $userIds = $role->users->pluck('id')->toArray();

            $role->delete();

            activity()
                ->withProperties([
                    'deleted_role_data' => $roleData,
                    'permissions' => $permissions,
                    'affected_users' => $userIds,
                ])
                ->log('Role deleted');

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function duplicateRole(Role $sourceRole, string $newName, ?string $newDescription = null): Role
    {
        DB::beginTransaction();

        try {
            $newRole = Role::create([
                'name' => $newName,
                'guard_name' => $sourceRole->guard_name,
                'description' => $newDescription ?? ($sourceRole->description . ' (Copy)'),
                'category' => $sourceRole->category,
                'is_protected' => false,
                'protection_level' => 'user',
                'created_by' => Auth::id(),
            ]);

            $permissions = $sourceRole->permissions->pluck('name')->toArray();
            $newRole->syncPermissions($permissions);

            activity()
                ->performedOn($newRole)
                ->withProperties([
                    'source_role' => $sourceRole->name,
                    'permissions' => $permissions,
                ])
                ->log('Role duplicated');

            DB::commit();

            return $newRole;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignPermissionsToRole(Role $role, array $permissionNames): void
    {
        if (! $this->securityService->canModifyRole($role)) {
            throw new \Exception(__('role_management.errors.cannot_modify_protected_role'));
        }

        $validationIssues = $this->securityService->validatePermissionRemoval($role, []);
        if (! empty($validationIssues)) {
            throw new \Exception(implode(' ', $validationIssues));
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->givePermissionTo($permissions);

        activity()
            ->performedOn($role)
            ->withProperties([
                'permissions' => $permissionNames,
            ])
            ->log('Permissions assigned to role');
    }

    public function removePermissionsFromRole(Role $role, array $permissionNames): void
    {
        if (! $this->securityService->canModifyRole($role)) {
            throw new \Exception(__('role_management.errors.cannot_modify_protected_role'));
        }

        $validationIssues = $this->securityService->validatePermissionRemoval($role, $permissionNames);
        if (! empty($validationIssues)) {
            throw new \Exception(implode(' ', $validationIssues));
        }

        foreach ($permissionNames as $permissionName) {
            if (! $this->securityService->canRemovePermissionFromRole($role, $permissionName)) {
                throw new \Exception(__('role_management.errors.cannot_remove_critical_permission', [
                    'permission' => $permissionName,
                ]));
            }
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->revokePermissionTo($permissions);

        activity()
            ->performedOn($role)
            ->withProperties([
                'permissions' => $permissionNames,
            ])
            ->log('Permissions removed from role');
    }

    public function syncRolePermissions(Role $role, array $permissionNames): void
    {
        if (! $this->securityService->canModifyRole($role)) {
            throw new \Exception(__('role_management.errors.cannot_modify_protected_role'));
        }

        $currentPermissions = $role->permissions->pluck('name')->toArray();
        $permissionsToRemove = array_diff($currentPermissions, $permissionNames);

        $validationIssues = $this->securityService->validatePermissionRemoval($role, $permissionsToRemove);
        if (! empty($validationIssues)) {
            throw new \Exception(implode(' ', $validationIssues));
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);

        activity()
            ->performedOn($role)
            ->withProperties([
                'previous_permissions' => $currentPermissions,
                'new_permissions' => $permissionNames,
            ])
            ->log('Role permissions synced');
    }

    public function bulkUpdateRoles(array $roleUpdates): array
    {
        $results = [];

        DB::beginTransaction();

        try {
            foreach ($roleUpdates as $roleId => $updateData) {
                $role = Role::findOrFail($roleId);
                $results[$roleId] = $this->updateRole($role, $updateData);
            }

            activity()
                ->withProperties([
                    'updated_roles' => array_keys($roleUpdates),
                    'update_data' => $roleUpdates,
                ])
                ->log('Roles bulk updated');

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getRolesByCategory(?string $category = null): Collection
    {
        $query = Role::with(['permissions', 'users']);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->orderBy('category')->orderBy('name')->get();
    }

    public function getAvailableCategories(): array
    {
        return Role::distinct('category')
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public function searchRoles(string $query, ?string $category = null): Collection
    {
        $search = Role::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        });

        if ($category) {
            $search->where('category', $category);
        }

        return $search->with(['permissions', 'users'])
            ->orderBy('name')
            ->get();
    }

    public function getRoleStatistics(): array
    {
        return [
            'total_roles' => Role::count(),
            'protected_roles' => Role::where('is_protected', true)->count(),
            'system_roles' => Role::where('protection_level', 'system')->count(),
            'admin_roles' => Role::where('protection_level', 'admin')->count(),
            'user_roles' => Role::where('protection_level', 'user')->count(),
            'roles_by_category' => Role::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'roles_with_users' => Role::has('users')->count(),
            'unused_roles' => Role::doesntHave('users')->count(),
            // Additional statistics for the view
            'users_with_roles' => User::has('roles')->count(),
            'total_permissions' => Permission::count(),
            'categories' => count($this->getAvailableCategories()),
        ];
    }

    public function getUserRoles(User $user): Collection
    {
        return $user->roles()->with('permissions')->get();
    }

    public function assignRoleToUser(User $user, Role $role): void
    {
        $user->assignRole($role);

        activity()
            ->performedOn($role)
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'user_email' => $user->email,
            ])
            ->log('Role assigned to user');
    }

    public function removeRoleFromUser(User $user, Role $role): void
    {
        if ($this->securityService->isLastSuperAdminRole($role) && $user->hasRole($role->name)) {
            $superAdminUsers = User::role($role->name)->where('id', '!=', $user->id)->count();
            if ($superAdminUsers < 1) {
                throw new \Exception(__('role_management.errors.cannot_remove_last_super_admin_from_user'));
            }
        }

        $user->removeRole($role);

        activity()
            ->performedOn($role)
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'user_email' => $user->email,
            ])
            ->log('Role removed from user');
    }

    public function validateRoleName(string $name, ?int $excludeRoleId = null): array
    {
        $errors = [];

        if (empty(trim($name))) {
            $errors[] = __('role_management.validation.name_required');
        }

        if (strlen($name) > 255) {
            $errors[] = __('role_management.validation.name_too_long');
        }

        if (! preg_match('/^[a-z0-9-_]+$/', $name)) {
            $errors[] = __('role_management.validation.name_invalid_format');
        }

        $existingRole = Role::where('name', $name);
        if ($excludeRoleId) {
            $existingRole->where('id', '!=', $excludeRoleId);
        }

        if ($existingRole->exists()) {
            $errors[] = __('role_management.validation.name_already_exists');
        }

        return $errors;
    }

    public function generateRoleName(string $baseName): string
    {
        $slug = Str::slug($baseName);
        $originalSlug = $slug;
        $counter = 1;

        while (Role::where('name', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Security validation methods - delegated to SecurityValidationService
    public function canDeleteRole(Role $role): bool
    {
        return $this->securityService->canDeleteRole($role);
    }

    public function canModifyRole(Role $role, ?User $user = null): bool
    {
        return $this->securityService->canModifyRole($role, $user);
    }

    public function validateRoleDeletion(Role $role): array
    {
        return $this->securityService->validateRoleDeletion($role);
    }

    public function getProtectedRoleInfo(Role $role): array
    {
        return $this->securityService->getProtectedRoleInfo($role);
    }

    public function getRoleDeletionImpact(Role $role): array
    {
        return $this->securityService->getRoleDeletionImpact($role);
    }

    // Data access methods for controller
    public function getAllRoles(array $filters = [])
    {
        $query = Role::with(['permissions', 'users'])
            ->withCount(['users', 'permissions']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['scope'])) {
            $query->where('scope', $filters['scope']);
        }

        if (! empty($filters['protection_level'])) {
            $query->where('protection_level', $filters['protection_level']);
        }

        return $query->orderBy('scope')->orderBy('name')->paginate(15)->appends($filters);
    }

    public function getProtectionLevels(): array
    {
        return [
            'system' => __('role_management.protection_levels.system'),
            'admin' => __('role_management.protection_levels.admin'),
            'user' => __('role_management.protection_levels.user'),
        ];
    }

    public function getAvailableScopes(): array
    {
        return [
            'system' => __('role_management.scopes.system'),
            'federation' => __('role_management.scopes.federation'),
            'entity' => __('role_management.scopes.entity'),
            'individual' => __('role_management.scopes.individual'),
        ];
    }

    public function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::orderBy('category')->orderBy('name')->get();
    }

    public function getAllPermissionsGrouped(): \Illuminate\Support\Collection
    {
        return Permission::with('roles')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    public function getActiveTemplates(): \Illuminate\Database\Eloquent\Collection
    {
        return RoleTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }
}
