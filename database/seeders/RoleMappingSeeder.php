<?php

namespace Database\Seeders;

use Domain\Certifications\Models\Certification;
use Domain\Licenses\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleMappingSeeder extends Seeder
{
    /**
     * Default role name mappings from SyncUserRolesAction.
     * Keys must be lowercase to match normalized professional role slugs.
     */
    private array $roleNameMappings = [
        'instructor' => 'individual-instructor',
        'coach' => 'individual-coach',
        'athlete' => 'individual-athlete',
        'technical_official' => 'individual-technical-official',
        'leader' => 'individual-leader',
        'diver' => 'individual-diver',
        'diving-instructor' => 'individual-diving-instructor',
        'divingprofessional' => 'individual-diving-pro',
        'instructor-trainer' => 'individual-instructor-trainer',
        'coach-trainer' => 'individual-coach-trainer',
    ];

    /**
     * Committee-specific primary role overrides.
     * DIVING/SCIENTIFIC certifications use international-specific roles instead of defaults.
     */
    private array $committeePrimaryRoleOverrides = [
        'DIVING' => [
            'instructor' => 'individual-cmas-pro',
            'instructor-trainer' => 'individual-cmas-pro',
            'diver' => 'individual-cmas-diver',
            'leader' => 'individual-cmas-diver',
        ],
        'SCIENTIFIC' => [
            'instructor' => 'individual-cmas-pro',
            'instructor-trainer' => 'individual-cmas-pro',
            'diver' => 'individual-cmas-diver',
        ],
    ];

    /**
     * Entity role mappings based on license committee
     */
    private array $entityRoleMappings = [
        'SPORT' => 'entity-sport',
        'DIVING' => 'entity-diving-services',
        'COACHING' => 'entity-coach-admin',
        'REFEREE' => 'entity-referee-admin',
    ];

    /**
     * Committee-specific view role mappings
     */
    private array $committeeViewRoles = [
        'DIVING' => [
            'instructor' => 'view-individual-diving-instructor',
            'instructor-trainer' => 'view-individual-diving-instructor-trainer',
        ],
        'COACHING' => [
            'coach' => 'view-individual-coach',
            'coach-trainer' => 'view-individual-coach-trainer',
        ],
        'SPORT' => [
            'technical_official' => 'view-individual-technical-official',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Role Mapping Seeder...');

        // Check if the required tables exist
        if (! $this->checkRequiredTables()) {
            return;
        }

        DB::beginTransaction();

        try {
            $this->seedAdminRoleExclusions();
            $this->seedFederationRoles();
            $this->seedLicenseRoles();
            $this->seedEntityLicenseRoles();
            $this->seedCertificationRoles();

            DB::commit();

            $this->command->info('Role Mapping Seeder completed successfully!');
            Log::info('RoleMappingSeeder completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding role mappings: ' . $e->getMessage());
            Log::error('RoleMappingSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if required pivot tables exist
     */
    private function checkRequiredTables(): bool
    {
        $requiredTables = ['license_roles', 'certification_roles', 'federation_roles'];
        $missingTables = [];

        foreach ($requiredTables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (! empty($missingTables)) {
            $this->command->error('Missing required tables: ' . implode(', ', $missingTables));
            $this->command->info('Please run the role refactor migrations first.');

            return false;
        }

        return true;
    }

    /**
     * Seed admin role exclusions
     *
     * This method documents which roles should be preserved during role syncing.
     * Admin and super admin roles are NOT managed through the role mapping system
     * to prevent accidental lockouts.
     */
    private function seedAdminRoleExclusions(): void
    {
        $this->command->info('Documenting admin role exclusions...');

        // List of admin roles that should NEVER be synced/removed by the role mapping system
        $adminRoles = [
            'admin',
            'federation-admin',
            'association-sport-admin',
            'association-scientific-admin',
            'association-admin',
            'association-territorial-admin',
            'entity-admin',
            'entity-sport',
            'entity-diving-services',
            'entity-international',
        ];

        // Log these exclusions for documentation purposes
        Log::info('Admin roles excluded from role mapping system', [
            'roles' => $adminRoles,
            'reason' => 'Admin roles are preserved to prevent accidental lockouts',
        ]);

        $this->command->info('✓ Admin role exclusions documented. These roles will be preserved during role syncing.');
    }

    /**
     * Seed federation roles
     */
    private function seedFederationRoles(): void
    {
        $this->command->info('Seeding federation roles...');

        // Create the individual-approved role if it doesn't exist
        $approvedRole = Role::firstOrCreate(
            ['name' => 'individual-approved', 'guard_name' => 'web'],
            ['name' => 'individual-approved', 'guard_name' => 'web']
        );

        // Create federation_roles entry with NULL federation_id
        DB::table('federation_roles')->insertOrIgnore([
            'role_id' => $approvedRole->id,
            'federation_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✓ Federation roles seeded');
    }

    /**
     * Seed license roles based on current professional role mappings
     */
    private function seedLicenseRoles(): void
    {
        $this->command->info('Seeding license roles...');

        $licenses = License::with('professionalRole')->get();
        $progressBar = $this->command->getOutput()->createProgressBar($licenses->count());
        $progressBar->start();

        $addedCount = 0;

        foreach ($licenses as $license) {
            // Get the professional role from the license relationship
            if (! $license->professionalRole) {
                $progressBar->advance();

                continue;
            }

            $professionalRoleSlug = strtolower($license->professionalRole->role);

            if (! $professionalRoleSlug) {
                $progressBar->advance();

                continue;
            }

            // Map to individual role name
            $individualRoleName = $this->roleNameMappings[$professionalRoleSlug] ?? 'individual-' . $professionalRoleSlug;

            // Find or create the role
            $role = Role::firstOrCreate(
                ['name' => $individualRoleName, 'guard_name' => 'web'],
                ['name' => $individualRoleName, 'guard_name' => 'web']
            );

            // Create the license_roles mapping
            $created = DB::table('license_roles')->insertOrIgnore([
                'license_id' => $license->id,
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($created) {
                $addedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✓ License roles seeded ({$addedCount} new mappings created)");
    }

    /**
     * Seed entity license roles based on committee mappings
     */
    private function seedEntityLicenseRoles(): void
    {
        $this->command->info('Seeding entity license roles...');

        // Get all entity licenses
        $entityLicenses = License::with('committee')
            ->where('requester_model', 'Domain\Entities\Models\Entity')
            ->get();

        if ($entityLicenses->isEmpty()) {
            $this->command->info('✓ No entity licenses found to seed');

            return;
        }

        $progressBar = $this->command->getOutput()->createProgressBar($entityLicenses->count());
        $progressBar->start();

        $addedCount = 0;

        foreach ($entityLicenses as $license) {
            if (! $license->committee) {
                $progressBar->advance();

                continue;
            }

            $committeeCode = $license->committee->code;

            // Map committee to entity role
            $entityRoleName = $this->entityRoleMappings[$committeeCode] ?? 'entity-admin';

            // Find or create the role
            $role = Role::firstOrCreate(
                ['name' => $entityRoleName, 'guard_name' => 'web'],
                ['name' => $entityRoleName, 'guard_name' => 'web']
            );

            // Create the license_roles mapping for entity license
            $created = DB::table('license_roles')->insertOrIgnore([
                'license_id' => $license->id,
                'role_id' => $role->id,
                'committee_id' => $license->committee->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($created) {
                $addedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✓ Entity license roles seeded ({$addedCount} new mappings created)");
    }

    /**
     * Seed certification roles based on current professional role mappings
     */
    private function seedCertificationRoles(): void
    {
        $this->command->info('Seeding certification roles...');

        $certifications = Certification::with(['professionalRole', 'committee'])->get();
        $progressBar = $this->command->getOutput()->createProgressBar($certifications->count());
        $progressBar->start();

        $addedCount = 0;
        $viewRolesCount = 0;

        foreach ($certifications as $certification) {
            // Get the professional role from the certification relationship
            if (! $certification->professionalRole) {
                $progressBar->advance();

                continue;
            }

            $professionalRoleSlug = strtolower($certification->professionalRole->role);

            if (! $professionalRoleSlug) {
                $progressBar->advance();

                continue;
            }

            // Resolve the primary role name using committee-specific overrides first,
            // then falling back to the default mapping
            $individualRoleName = $this->resolvePrimaryRoleName(
                $professionalRoleSlug,
                $certification->committee?->code
            );

            // Find or create the primary role
            $role = Role::firstOrCreate(
                ['name' => $individualRoleName, 'guard_name' => 'web'],
                ['name' => $individualRoleName, 'guard_name' => 'web']
            );

            // Create the certification_roles mapping for the primary role
            $created = DB::table('certification_roles')->insertOrIgnore([
                'certification_id' => $certification->id,
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($created) {
                $addedCount++;
            }

            // Handle committee-specific view roles
            if ($certification->committee) {
                $committeeCode = $certification->committee->code;

                if ($committeeCode && isset($this->committeeViewRoles[$committeeCode])) {
                    $viewRoleMapping = $this->committeeViewRoles[$committeeCode];

                    if (isset($viewRoleMapping[$professionalRoleSlug])) {
                        $viewRoleName = $viewRoleMapping[$professionalRoleSlug];

                        // Find or create the view role
                        $viewRole = Role::firstOrCreate(
                            ['name' => $viewRoleName, 'guard_name' => 'web'],
                            ['name' => $viewRoleName, 'guard_name' => 'web']
                        );

                        // Create the certification_roles mapping for the view role with committee_id
                        $viewCreated = DB::table('certification_roles')->insertOrIgnore([
                            'certification_id' => $certification->id,
                            'role_id' => $viewRole->id,
                            'committee_id' => $certification->committee->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        if ($viewCreated) {
                            $viewRolesCount++;
                        }
                    }
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✓ Certification roles seeded ({$addedCount} primary mappings, {$viewRolesCount} view mappings created)");
    }

    /**
     * Resolve the primary role name for a certification, checking committee-specific
     * overrides first, then falling back to the default role name mapping.
     */
    private function resolvePrimaryRoleName(string $professionalRoleSlug, ?string $committeeCode): string
    {
        if ($committeeCode && isset($this->committeePrimaryRoleOverrides[$committeeCode][$professionalRoleSlug])) {
            return $this->committeePrimaryRoleOverrides[$committeeCode][$professionalRoleSlug];
        }

        return $this->roleNameMappings[$professionalRoleSlug] ?? 'individual-' . $professionalRoleSlug;
    }
}
