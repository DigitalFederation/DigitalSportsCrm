<?php

namespace Domain\Permissions\Actions;

use App\Models\Permission;
use Domain\Permissions\Exceptions\PermissionAlreadyExistsException;
use Illuminate\Support\Facades\DB;

class CreatePermissionAction
{
    public static function execute(array $data): Permission
    {
        // Check if permission already exists
        if (Permission::where('name', $data['name'])->exists()) {
            throw new PermissionAlreadyExistsException("Permission '{$data['name']}' already exists");
        }

        return DB::transaction(function () use ($data) {
            $permission = Permission::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Clear permission cache
            app()['cache']->forget('spatie.permission.cache');

            return Permission::findOrFail($permission->id);
        });
    }
}
