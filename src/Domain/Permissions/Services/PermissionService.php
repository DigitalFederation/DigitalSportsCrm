<?php

namespace Domain\Permissions\Services;

use App\Models\Permission;
use Domain\Permissions\Actions\BulkCreatePermissionsAction;
use Domain\Permissions\Actions\CreatePermissionAction;
use Domain\Permissions\Actions\DeletePermissionAction;
use Domain\Permissions\Actions\ExportPermissionsToCsvAction;
use Domain\Permissions\Actions\ImportPermissionsFromCsvAction;
use Domain\Permissions\Actions\UpdatePermissionAction;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * Get all permissions with optional filters
     */
    public function getAllPermissions(array $filters = []): Collection
    {
        $query = Permission::query();

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('category')->orderBy('name')->get();
    }

    /**
     * Get permission categories
     */
    public function getCategories(): array
    {
        return Permission::distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Create a new permission
     */
    public function createPermission(array $data): Permission
    {
        return CreatePermissionAction::execute($data);
    }

    /**
     * Update an existing permission
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        return UpdatePermissionAction::execute($permission, $data);
    }

    /**
     * Delete a permission
     */
    public function deletePermission(Permission $permission): bool
    {
        return DeletePermissionAction::execute($permission);
    }

    /**
     * Bulk create permissions
     */
    public function bulkCreatePermissions(array $permissions): array
    {
        return BulkCreatePermissionsAction::execute($permissions);
    }

    /**
     * Import permissions from CSV
     */
    public function importFromCsv(string $filePath, bool $skipExisting = true): array
    {
        return ImportPermissionsFromCsvAction::execute($filePath, $skipExisting);
    }

    /**
     * Export permissions to CSV
     */
    public function exportToCsv(array $filters = []): string
    {
        return ExportPermissionsToCsvAction::execute($filters);
    }

    /**
     * Check if a permission is a system permission
     */
    public function isSystemPermission(string $permissionName): bool
    {
        $systemPermissions = [
            'access users',
            'manage roles',
            'manage permissions',
            'manage role permissions',
            'manage protected roles',
            'access role management dashboard',
            'manage user roles',
        ];

        return in_array($permissionName, $systemPermissions);
    }
}
