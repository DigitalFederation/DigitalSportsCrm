<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddManageRoutePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the permission if it doesn't exist
        $permission = Permission::firstOrCreate(
            [
                'name' => 'manage-route-permissions',
                'guard_name' => 'web',
            ],
            [
                'category' => 'System',
                'description' => 'Manage route permission mappings',
            ]
        );

        // If permission was just created, update category and description
        if (! $permission->wasRecentlyCreated && (! $permission->category || ! $permission->description)) {
            $permission->update([
                'category' => 'System',
                'description' => 'Manage route permission mappings',
            ]);
        }

        // Assign the permission to admin role
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole && ! $adminRole->hasPermissionTo('manage-route-permissions')) {
            $adminRole->givePermissionTo($permission);
            $this->command->info('Permission "manage-route-permissions" assigned to admin role.');
        } elseif ($adminRole) {
            $this->command->info('admin already has the "manage-route-permissions" permission.');
        } else {
            $this->command->warn('admin role not found. Permission created but not assigned.');
        }

        $this->command->info('AddManageRoutePermissionsSeeder completed successfully.');
    }
}
