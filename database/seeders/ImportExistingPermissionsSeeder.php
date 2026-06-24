<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RoutePermission;
use Domain\Permissions\Services\RoutePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;

class ImportExistingPermissionsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔄 Importing existing hardcoded permissions into dynamic system...');

        $routeService = app(RoutePermissionService::class);
        $routes = $routeService->scanRoutes();

        $imported = 0;
        $skipped = 0;

        foreach ($routes as $route) {
            // Skip if route has no name
            if (empty($route['name'])) {
                continue;
            }

            // Look for permission middleware in the route
            $permission = null;
            foreach ($route['middleware'] as $middleware) {
                if (str_starts_with($middleware, 'permission:')) {
                    $permission = str_replace('permission:', '', $middleware);
                    break;
                }
            }

            // Skip if no permission middleware found
            if (! $permission) {
                continue;
            }

            // Check if mapping already exists
            $existing = RoutePermission::where('route_pattern', $route['name'])->first();

            if ($existing) {
                $skipped++;
                $this->command->info("⏭️  Skipped {$route['name']} - already mapped");

                continue;
            }

            // Create the route permission mapping
            RoutePermission::create([
                'route_pattern' => $route['name'],
                'permission_name' => $permission,
                'middleware' => $route['middleware'],
                'is_active' => true,
            ]);

            $imported++;
            $this->command->info("✅ Imported {$route['name']} -> {$permission}");
        }

        $this->command->info("✅ Import complete: {$imported} imported, {$skipped} skipped");

        // Create commonly used permissions if they don't exist
        $commonPermissions = [
            // Users & Roles
            ['name' => 'access-users', 'description' => 'View users list', 'category' => 'Users'],
            ['name' => 'manage-users', 'description' => 'Create, edit, and delete users', 'category' => 'Users'],
            ['name' => 'manage-user-roles', 'description' => 'Assign roles to users', 'category' => 'Users'],
            ['name' => 'impersonate-users', 'description' => 'Impersonate other users', 'category' => 'Users'],

            // Federations
            ['name' => 'access-federations', 'description' => 'View federations list', 'category' => 'Federations'],
            ['name' => 'manage-federations', 'description' => 'Create, edit, and delete federations', 'category' => 'Federations'],

            // Entities
            ['name' => 'access-entities', 'description' => 'View entities list', 'category' => 'Entities'],
            ['name' => 'create-entities', 'description' => 'Create new entities', 'category' => 'Entities'],
            ['name' => 'edit-entities', 'description' => 'Edit existing entities', 'category' => 'Entities'],
            ['name' => 'delete-entities', 'description' => 'Delete entities', 'category' => 'Entities'],

            // Individuals
            ['name' => 'access-individuals', 'description' => 'View individuals list', 'category' => 'Individuals'],
            ['name' => 'create-individuals', 'description' => 'Create new individuals', 'category' => 'Individuals'],
            ['name' => 'edit-individuals', 'description' => 'Edit existing individuals', 'category' => 'Individuals'],
            ['name' => 'delete-individuals', 'description' => 'Delete individuals', 'category' => 'Individuals'],

            // Memberships
            ['name' => 'access-memberships', 'description' => 'View memberships', 'category' => 'Memberships'],
            ['name' => 'manage-memberships', 'description' => 'Manage membership plans and subscriptions', 'category' => 'Memberships'],

            // Certifications
            ['name' => 'access-certifications', 'description' => 'View certifications', 'category' => 'Certifications'],
            ['name' => 'manage-certifications', 'description' => 'Create and manage certifications', 'category' => 'Certifications'],
            ['name' => 'access-sport-certifications-attributed', 'description' => 'View sport certifications', 'category' => 'Certifications'],
            ['name' => 'access-scientific-certifications-attributed', 'description' => 'View scientific certifications', 'category' => 'Certifications'],
            ['name' => 'access-diving-certifications-attributed', 'description' => 'View diving certifications', 'category' => 'Certifications'],

            // Licenses
            ['name' => 'access-licenses', 'description' => 'View licenses', 'category' => 'Licenses'],
            ['name' => 'manage-licenses', 'description' => 'Create and manage licenses', 'category' => 'Licenses'],

            // Events
            ['name' => 'access-events', 'description' => 'View events', 'category' => 'Events'],
            ['name' => 'manage-events', 'description' => 'Create and manage events', 'category' => 'Events'],

            // Documents
            ['name' => 'access-documents', 'description' => 'View documents', 'category' => 'Documents'],
            ['name' => 'create-payment-documents', 'description' => 'Create payment documents', 'category' => 'Documents'],
            ['name' => 'access-official-documents', 'description' => 'View official documents', 'category' => 'Documents'],
            ['name' => 'access-federation-official-documents', 'description' => 'View federation official documents', 'category' => 'Documents'],

            // Products
            ['name' => 'access-products', 'description' => 'View products', 'category' => 'Products'],
            ['name' => 'manage-products', 'description' => 'Create and manage products', 'category' => 'Products'],

            // Settings
            ['name' => 'access-settings', 'description' => 'Access system settings', 'category' => 'Settings'],
            ['name' => 'manage-settings', 'description' => 'Modify system settings', 'category' => 'Settings'],

            // Diving
        ];

        $this->command->info('📋 Creating common permissions...');

        foreach ($commonPermissions as $permData) {
            $exists = Permission::where('name', $permData['name'])->exists();

            if (! $exists) {
                Permission::create([
                    'name' => $permData['name'],
                    'guard_name' => 'web',
                    'description' => $permData['description'],
                    'category' => $permData['category'],
                ]);
                $this->command->info("✅ Created permission: {$permData['name']}");
            } else {
                $this->command->info("⏭️  Permission already exists: {$permData['name']}");
            }
        }

        $this->command->info('✅ Permission import completed!');
        $this->command->warn('⚠️  IMPORTANT: You can now start replacing hardcoded permissions in routes with the dynamic middleware.');
        $this->command->warn('Example: Replace middleware(\'permission:access users\') with middleware(\'route.permission\')');
    }
}
