<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EnhanceRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateSystemRoles();
        $this->updatePermissionCategories();
        $this->createRoleTemplates();
        $this->createRoleManagementPermissions();
    }

    private function updateSystemRoles(): void
    {
        $systemRoles = [
            'admin' => ['system', 'System', 'Full system access with all administrative privileges'],
        ];

        foreach ($systemRoles as $roleName => [$protectionLevel, $category, $description]) {
            Role::where('name', $roleName)->update([
                'is_protected' => true,
                'protection_level' => $protectionLevel,
                'category' => $category,
                'description' => $description,
            ]);
        }

        $adminRoles = [
            'federation-admin' => ['admin', 'Federation', 'Federation management and administration'],
            'association-sport-admin' => ['admin', 'Association', 'Sports administration across associations'],
            'association-scientific-admin' => ['admin', 'Association', 'Scientific administration across associations'],
            'association-admin' => ['admin', 'Association', 'CMAS/Diving administration across associations'],
            'association-territorial-admin' => ['admin', 'Association', 'Territorial administration'],
        ];

        foreach ($adminRoles as $roleName => [$protectionLevel, $category, $description]) {
            Role::where('name', $roleName)->update([
                'is_protected' => true,
                'protection_level' => $protectionLevel,
                'category' => $category,
                'description' => $description,
            ]);
        }

        $entityRoles = [
            'entity-admin' => ['Entity', 'Entity management and administration'],
            'entity-sport' => ['Entity', 'Entity sports management'],
            'entity-diving-services' => ['Entity', 'Entity diving services management'],
            'entity-international' => ['Entity', 'Entity CMAS operations'],
        ];

        foreach ($entityRoles as $roleName => [$category, $description]) {
            Role::where('name', $roleName)->update([
                'category' => $category,
                'description' => $description,
            ]);
        }

        $individualRoles = [
            'individual-approved' => ['Individual', 'Basic approved individual status'],
            'individual-coach' => ['Individual', 'Coach certification and privileges'],
            'individual-instructor' => ['Individual', 'Instructor certification and privileges'],
            'individual-athlete' => ['Individual', 'Athlete status and privileges'],
        ];

        foreach ($individualRoles as $roleName => [$category, $description]) {
            Role::where('name', $roleName)->update([
                'category' => $category,
                'description' => $description,
            ]);
        }
    }

    private function updatePermissionCategories(): void
    {
        $permissionCategories = [
            'access users' => ['User Management', 'Access user management interface'],
            'manage user roles' => ['User Management', 'Manage user roles and permissions'],
            'create user' => ['User Management', 'Create new users'],
            'edit user' => ['User Management', 'Edit existing users'],
            'delete user' => ['User Management', 'Delete users'],
            'impersonate user' => ['User Management', 'Impersonate other users'],

            'access federations' => ['Federation Management', 'Access federation management interface'],
            'create federation' => ['Federation Management', 'Create new federations'],
            'edit federation' => ['Federation Management', 'Edit existing federations'],
            'delete federation' => ['Federation Management', 'Delete federations'],
            'manage federation membership' => ['Federation Management', 'Manage federation memberships'],

            'access entities' => ['Entity Management', 'Access entity management interface'],
            'create entities' => ['Entity Management', 'Create new entities'],
            'edit entities' => ['Entity Management', 'Edit existing entities'],
            'delete entities' => ['Entity Management', 'Delete entities'],
            'manage entity membership' => ['Entity Management', 'Manage entity memberships'],

            'access individuals' => ['Individual Management', 'Access individual management interface'],
            'create individual' => ['Individual Management', 'Create new individuals'],
            'edit individual' => ['Individual Management', 'Edit existing individuals'],
            'delete individual' => ['Individual Management', 'Delete individuals'],
            'manage individual roles' => ['Individual Management', 'Manage individual roles'],

            'access licenses' => ['License Management', 'Access license management interface'],
            'access licenses manager' => ['License Management', 'Access license manager features'],
            'create license' => ['License Management', 'Create new licenses'],
            'edit license' => ['License Management', 'Edit existing licenses'],
            'delete license' => ['License Management', 'Delete licenses'],

            'access certifications' => ['Certification Management', 'Access certification management interface'],
            'manage certifications' => ['Certification Management', 'Manage certifications'],
            'create certification' => ['Certification Management', 'Create new certifications'],
            'edit certification' => ['Certification Management', 'Edit existing certifications'],
            'delete certification' => ['Certification Management', 'Delete certifications'],

            'access official documents' => ['Document Management', 'Access official documents'],
            'access personal documents' => ['Document Management', 'Access personal documents'],
            'download reports' => ['Document Management', 'Download reports'],
            'generate documents' => ['Document Management', 'Generate new documents'],
            'manage document templates' => ['Document Management', 'Manage document templates'],

            'access sport menu' => ['Menu and Navigation', 'Access sport menu'],
            'access diving menu' => ['Menu and Navigation', 'Access diving menu'],
            'access scientific menu' => ['Menu and Navigation', 'Access scientific menu'],
            'access admin menu' => ['Menu and Navigation', 'Access admin menu'],
            'manage_menus' => ['Menu and Navigation', 'Manage dynamic menus'],

            'access system settings' => ['System Administration', 'Access system settings'],
            'manage system configuration' => ['System Administration', 'Manage system configuration'],
            'view system logs' => ['System Administration', 'View system logs'],
            'manage system maintenance' => ['System Administration', 'Manage system maintenance'],
        ];

        foreach ($permissionCategories as $permissionName => [$category, $description]) {
            Permission::where('name', $permissionName)->update([
                'category' => $category,
                'description' => $description,
            ]);
        }
    }

    private function createRoleTemplates(): void
    {
        $templates = [
            [
                'name' => 'Basic Federation Admin',
                'description' => 'Standard federation administrator template with basic permissions',
                'permissions' => json_encode([
                    'access federations',
                    'manage federation membership',
                    'access individuals',
                    'access entities',
                    'access official documents',
                ]),
                'category' => 'Federation',
                'is_active' => true,
            ],
            [
                'name' => 'Basic Entity Admin',
                'description' => 'Standard entity administrator template with basic permissions',
                'permissions' => json_encode([
                    'access entities',
                    'manage entity membership',
                    'access individuals',
                    'access personal documents',
                ]),
                'category' => 'Entity',
                'is_active' => true,
            ],
            [
                'name' => 'Basic Individual Role',
                'description' => 'Standard individual role template with basic permissions',
                'permissions' => json_encode([
                    'access personal documents',
                    'view personal profile',
                ]),
                'category' => 'Individual',
                'is_active' => true,
            ],
            [
                'name' => 'Sports Administrator',
                'description' => 'Sports-focused administrator template',
                'permissions' => json_encode([
                    'access sport menu',
                    'access individuals',
                    'access entities',
                    'access certifications',
                    'access licenses',
                ]),
                'category' => 'Sports',
                'is_active' => true,
            ],
            [
                'name' => 'Diving Administrator',
                'description' => 'Diving-focused administrator template',
                'permissions' => json_encode([
                    'access diving menu',
                    'access individuals',
                    'access entities',
                    'access certifications',
                    'access licenses',
                ]),
                'category' => 'Diving',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('role_templates')->updateOrInsert(
                ['name' => $template['name']],
                $template
            );
        }
    }

    private function createRoleManagementPermissions(): void
    {
        $newPermissions = [
            [
                'name' => 'manage roles',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'Create, edit, and delete roles',
            ],
            [
                'name' => 'manage role permissions',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'Assign and remove permissions from roles',
            ],
            [
                'name' => 'manage permissions',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'Create, edit, and delete permissions',
            ],
            [
                'name' => 'manage route permissions',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'Manage route-permission mappings',
            ],
            [
                'name' => 'view role audit logs',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'View role and permission change audit logs',
            ],
            [
                'name' => 'access role management dashboard',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'Access role management dashboard',
            ],
            [
                'name' => 'manage protected roles',
                'guard_name' => 'web',
                'category' => 'Role Management',
                'description' => 'Manage protected system roles (super-admin only)',
            ],
        ];

        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                $permission
            );
        }

        $superAdminRole = Role::where('name', 'admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(collect($newPermissions)->pluck('name')->toArray());
        }
    }
}
