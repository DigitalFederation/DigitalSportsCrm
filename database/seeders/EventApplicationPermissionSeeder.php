<?php

namespace Database\Seeders;

use App\Models\RoutePermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Event Application Permission Seeder
 *
 * Seeds permissions and route bindings for the Event Applications (Candidaturas) module.
 * This module handles both federation-initiated applications and direct submissions from entities.
 */
class EventApplicationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Event Application permissions...');

        // Create permissions
        $manageTemplates = Permission::firstOrCreate(
            ['name' => 'manage application templates', 'guard_name' => 'web'],
            [
                'category' => 'Event Applications',
                'description' => 'Create and manage application templates for federation-initiated calls',
            ]
        );

        $reviewApplications = Permission::firstOrCreate(
            ['name' => 'review event applications', 'guard_name' => 'web'],
            [
                'category' => 'Event Applications',
                'description' => 'Review, approve, and reject event applications',
            ]
        );

        $publishApplications = Permission::firstOrCreate(
            ['name' => 'publish event applications', 'guard_name' => 'web'],
            [
                'category' => 'Event Applications',
                'description' => 'Publish approved applications to the event calendar',
            ]
        );

        $createApplications = Permission::firstOrCreate(
            ['name' => 'create event applications', 'guard_name' => 'web'],
            [
                'category' => 'Event Applications',
                'description' => 'Create and submit event applications',
            ]
        );

        $viewOwnApplications = Permission::firstOrCreate(
            ['name' => 'view own event applications', 'guard_name' => 'web'],
            [
                'category' => 'Event Applications',
                'description' => 'View own submitted event applications',
            ]
        );

        $this->command->info('Permissions created successfully.');

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Create route permission bindings
        $this->createRoutePermissions();

        $this->command->info('Event Application permissions seeded successfully!');
    }

    /**
     * Assign permissions to appropriate roles
     */
    private function assignPermissionsToRoles(): void
    {
        $this->command->info('Assigning permissions to roles...');

        // Admin gets all permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                'manage application templates',
                'review event applications',
                'publish event applications',
                'create event applications',
                'view own event applications',
            ]);
        }

        // Association Sport Admin - can review applications
        $associationSportAdmin = Role::where('name', 'association-sport-admin')->first();
        if ($associationSportAdmin) {
            $associationSportAdmin->givePermissionTo([
                'review event applications',
                'publish event applications',
            ]);
        }

        // Association Admin - can review applications
        $associationCmasAdmin = Role::where('name', 'association-admin')->first();
        if ($associationCmasAdmin) {
            $associationCmasAdmin->givePermissionTo([
                'review event applications',
                'publish event applications',
            ]);
        }

        // Association Scientific Admin - can review applications
        $associationScientificAdmin = Role::where('name', 'association-scientific-admin')->first();
        if ($associationScientificAdmin) {
            $associationScientificAdmin->givePermissionTo([
                'review event applications',
                'publish event applications',
            ]);
        }

        // Federation Admin - can view templates and create applications
        $federationAdmin = Role::where('name', 'federation-admin')->first();
        if ($federationAdmin) {
            $federationAdmin->givePermissionTo([
                'create event applications',
                'view own event applications',
            ]);
        }

        // Entity Admin - can create and view applications
        $entityAdmin = Role::where('name', 'entity-admin')->first();
        if ($entityAdmin) {
            $entityAdmin->givePermissionTo([
                'create event applications',
                'view own event applications',
            ]);
        }

        // Entity Sport - can create applications
        $entitySport = Role::where('name', 'entity-sport')->first();
        if ($entitySport) {
            $entitySport->givePermissionTo([
                'create event applications',
                'view own event applications',
            ]);
        }

        // Entity Diving Services - can create applications
        $entityDivingServices = Role::where('name', 'entity-diving-services')->first();
        if ($entityDivingServices) {
            $entityDivingServices->givePermissionTo([
                'create event applications',
                'view own event applications',
            ]);
        }

        $this->command->info('Permissions assigned to roles successfully.');
    }

    /**
     * Create route permission bindings
     */
    private function createRoutePermissions(): void
    {
        $this->command->info('Creating route permission bindings...');

        // Admin routes - Application Management
        $adminApplicationRoutes = [
            'admin/event-applications',
            'admin/event-applications/*',
        ];

        foreach ($adminApplicationRoutes as $route) {
            RoutePermission::updateOrCreate(
                [
                    'route_pattern' => $route,
                    'permission_name' => 'review event applications',
                ],
                [
                    'middleware' => ['auth', 'permission:review event applications'],
                    'is_active' => true,
                ]
            );
        }

        // Entity routes - Event Applications
        $entityApplicationRoutes = [
            'entity/event-applications',
            'entity/event-applications/*',
        ];

        foreach ($entityApplicationRoutes as $route) {
            RoutePermission::updateOrCreate(
                [
                    'route_pattern' => $route,
                    'permission_name' => 'create event applications',
                ],
                [
                    'middleware' => ['auth', 'permission:create event applications'],
                    'is_active' => true,
                ]
            );
        }

        // Federation routes - Event Applications
        $federationApplicationRoutes = [
            'federation/event-applications',
            'federation/event-applications/*',
        ];

        foreach ($federationApplicationRoutes as $route) {
            RoutePermission::updateOrCreate(
                [
                    'route_pattern' => $route,
                    'permission_name' => 'create event applications',
                ],
                [
                    'middleware' => ['auth', 'permission:create event applications'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Route permission bindings created successfully.');
    }
}
