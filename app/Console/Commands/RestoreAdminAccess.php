<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RestoreAdminAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:admin-access
                            {--email= : The email of the user to grant admin access}
                            {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'EMERGENCY: Restore admin access to a user by directly assigning admin role and critical permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠️  EMERGENCY ADMIN ACCESS RESTORATION ⚠️');
        $this->warn('This command bypasses normal permission checks and should only be used in emergencies.');
        $this->newLine();

        $email = trim((string) $this->option('email'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid --email option is required.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error('No user found for the provided email.');

            return self::FAILURE;
        }

        $this->info("Found user: {$user->name} ({$user->email})");

        if (! $this->option('force') && ! $this->confirm("Grant full admin access to {$user->email}?")) {
            $this->warn('Operation cancelled.');

            return self::FAILURE;
        }

        // Find or create admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $this->info("Admin role ready: {$adminRole->name}");

        // Assign admin role
        if (! $user->hasRole('admin')) {
            $user->assignRole($adminRole);
            $this->info('✓ Admin role assigned');
        } else {
            $this->info('✓ User already has admin role');
        }

        // Find and assign other critical roles
        $criticalRoles = [
            'access users',
            'manage user roles',
            'access federations',
            'access memberships',
            'access products',
            'access entities',
            'access individuals',
            'access events',
            'access certifications',
            'access sport certifications',
            'access diving and scientific certifications',
            'access diving and scientific certifications manager',
            'access sport certifications attributed',
            'access sport certifications manager',
            'access scientific certifications attributed',
            'access diving certifications attributed',
            'access certification slots manager',
            'access licenses',
            'access licenses manager',
            'access diving certification attributed',
            'access sport individual licenses attributed',
            'access diving individual licenses attributed',
            'access scientific individual licenses attributed',
            'access sport entity licenses attributed',
            'access diving entity licenses attributed',
            'access scientific entity licenses attributed',
            'access documents',
            'create payment documents',
            'access official documents',
            'access diving location',
            'access attachments menu',
            'access diving attachments',
            'access sport attachments',
            'access scientific attachments',
            'access settings',
            'download reports',
            'access files area menu',
            'impersonate users',
        ];

        foreach ($criticalRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && ! $user->hasRole($roleName)) {
                $user->assignRole($role);
                $this->info("✓ Assigned role: {$roleName}");
            }
        }

        // Give all permissions directly to ensure full access
        $allPermissions = Permission::all();
        $user->givePermissionTo($allPermissions);
        $this->info("✓ Assigned all {$allPermissions->count()} permissions directly");

        // Clear caches
        $this->call('cache:clear');
        $this->call('permission:cache-reset');
        $this->info('✓ Caches cleared');

        $this->newLine();
        $this->info('🚨 EMERGENCY ACCESS RESTORED 🚨');
        $this->info("User: {$user->email}");
        $this->info('Roles: ' . $user->roles->pluck('name')->join(', '));
        $this->info("Direct permissions: {$user->permissions->count()}");

        $this->newLine();
        $this->warn('Please review and adjust permissions through the normal UI once access is restored.');

        return self::SUCCESS;
    }
}
