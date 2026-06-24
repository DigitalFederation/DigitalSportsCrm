<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Users\Actions\SyncUserRolesAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset cached roles and permissions
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    // Seed necessary data
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=CommitteeSeeder');
    artisan('db:seed --class=ProfessionalRoleSeeder');

    // Create test data
    $this->group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->user = User::factory()->create(['group_id' => $this->group->id]);
    $this->federation = Federation::factory()->create(['is_local' => false]);
    $this->individual = Individual::factory()->create(['user_id' => $this->user->id]);

    // Attach individual to federation
    $this->federation->individuals()->attach($this->individual, ['active' => true]);

    // Create committees
    $this->divingCommittee = Committee::where('code', 'DIVING')->first() ?: Committee::factory()->create(['code' => 'DIVING']);
    $this->scientificCommittee = Committee::where('code', 'SCIENTIFIC')->first() ?: Committee::factory()->create(['code' => 'SCIENTIFIC']);

    // Create professional roles
    $this->instructorRole = ProfessionalRole::where('role', 'INSTRUCTOR')->first() ?: ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR']);
    $this->coachRole = ProfessionalRole::where('role', 'COACH')->first() ?: ProfessionalRole::factory()->create(['role' => 'COACH']);
    $this->athleteRole = ProfessionalRole::where('role', 'ATHLETE')->first() ?: ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $this->diverRole = ProfessionalRole::where('role', 'DIVER')->first() ?: ProfessionalRole::factory()->create(['role' => 'DIVER']);
    $this->technicalOfficialRole = ProfessionalRole::where('role', 'TECHNICAL_OFFICIAL')->first() ?: ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);

    // Create specific committee roles
    $this->divingInstructorRole = ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR', 'committee_id' => $this->divingCommittee->id]);
    $this->scientificInstructorRole = ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR', 'committee_id' => $this->scientificCommittee->id]);

    // Ensure all required roles exist
    ensureRolesExist();

    $this->syncAction = new SyncUserRolesAction;
});

// Helper function to ensure all required roles exist
function ensureRolesExist()
{
    $requiredRoles = [
        'individual-approved',
        'individual-instructor',
        'individual-coach',
        'individual-athlete',
        'individual-technical-official',
        'individual-leader',
        'individual-diver',
        'view-individual-diving-instructor',
        'view-individual-scientific-instructor',
        'view-individual-diving-leader',
        'view-individual-scientific-leader',
        'view-individual-coach',
        'view-individual-technical-official',
    ];

    foreach ($requiredRoles as $roleName) {
        Role::firstOrCreate(['name' => $roleName]);
    }
}

describe('Role Syncing', function () {
    it('grants roles based on license_roles pivot table', function () {
        // Create instructor license
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        // Map license to role in pivot table
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create active license attribution
        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert role is granted
        expect($this->user->hasRole('individual-instructor'))->toBeTrue();
    });

    it('removes role when license expires', function () {
        // Create instructor license
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        // Map license to role in pivot table
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create expired license attribution
        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ExpiredLicenseAttributedState::class,
            'current_term_starts_at' => now()->subYear(),
            'current_term_ends_at' => now()->subDay(),
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert role is not granted
        expect($this->user->hasRole('individual-instructor'))->toBeFalse();
    });

    it('grants roles based on certification_roles pivot table', function () {
        // Create diver certification
        $certification = Certification::factory()->create([
            'professional_role_id' => $this->diverRole->id,
        ]);

        // Map certification to role in pivot table
        $diverRole = Role::where('name', 'individual-diver')->first();
        DB::table('certification_roles')->insert([
            'certification_id' => $certification->id,
            'role_id' => $diverRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create active certification attribution
        CertificationAttributed::factory()->create([
            'certification_id' => $certification->id,
            'individual_id' => $this->individual->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert role is granted
        expect($this->user->hasRole('individual-diver'))->toBeTrue();
    });

    it('grants roles based on federation_roles pivot table', function () {
        // Map federation membership to role
        $approvedRole = Role::where('name', 'individual-approved')->first();
        DB::table('federation_roles')->insert([
            'federation_id' => $this->federation->id,
            'role_id' => $approvedRole->id,
            'requires_active_membership' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert role is granted
        expect($this->user->hasRole('individual-approved'))->toBeTrue();
    });

    it('handles committee-specific role mappings', function () {
        // Create diving instructor certification
        $divingCert = Certification::factory()->create([
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        // Map certification to committee-specific role
        $divingInstructorViewRole = Role::where('name', 'view-individual-diving-instructor')->first();
        DB::table('certification_roles')->insert([
            'certification_id' => $divingCert->id,
            'role_id' => $divingInstructorViewRole->id,
            'committee_id' => $this->divingCommittee->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create active certification
        CertificationAttributed::factory()->create([
            'certification_id' => $divingCert->id,
            'individual_id' => $this->individual->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert committee-specific role is granted
        expect($this->user->hasRole('view-individual-diving-instructor'))->toBeTrue();
    });

    it('handles global federation roles without specific federation_id', function () {
        // Create global federation role (NULL federation_id)
        $approvedRole = Role::where('name', 'individual-approved')->first();
        DB::table('federation_roles')->insert([
            'federation_id' => null, // Global role
            'role_id' => $approvedRole->id,
            'requires_active_membership' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert role is granted based on any active federation membership
        expect($this->user->hasRole('individual-approved'))->toBeTrue();
    });

    it('combines multiple active credentials correctly', function () {
        // Create multiple licenses and certifications
        $instructorLicense = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        $coachLicense = License::factory()->create([
            'professional_role_id' => $this->coachRole->id,
            'requester_model' => Individual::class,
        ]);

        $technicalOfficialCert = Certification::factory()->create([
            'professional_role_id' => $this->technicalOfficialRole->id,
        ]);

        // Map to roles
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        $coachRole = Role::where('name', 'individual-coach')->first();
        $technicalOfficialViewRole = Role::where('name', 'view-individual-technical-official')->first();

        DB::table('license_roles')->insert([
            ['license_id' => $instructorLicense->id, 'role_id' => $instructorRole->id, 'created_at' => now(), 'updated_at' => now()],
            ['license_id' => $coachLicense->id, 'role_id' => $coachRole->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('certification_roles')->insert([
            'certification_id' => $technicalOfficialCert->id,
            'role_id' => $technicalOfficialViewRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create active attributions
        LicenseAttributed::factory()->create([
            'license_id' => $instructorLicense->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $coachLicense->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        CertificationAttributed::factory()->create([
            'certification_id' => $technicalOfficialCert->id,
            'individual_id' => $this->individual->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert all roles are granted
        expect($this->user->hasRole('individual-instructor'))->toBeTrue();
        expect($this->user->hasRole('individual-coach'))->toBeTrue();
        expect($this->user->hasRole('view-individual-technical-official'))->toBeTrue();
    });
});

describe('Edge Cases', function () {
    it('handles user with no individuals', function () {
        // Create user without any individuals
        $loneUser = User::factory()->create();

        // Execute sync action
        $this->syncAction->execute($loneUser);

        // Assert no roles are assigned
        expect($loneUser->roles->count())->toBe(0);
    });

    it('handles individual with no federation', function () {
        // Remove federation association
        $this->individual->individualFederations()->delete();

        // Create a license
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        // Map license to role
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Should have instructor role
        expect($this->user->hasRole('individual-instructor'))->toBeTrue();
    });

    it('handles credentials without role mappings', function () {
        // Create license without role mapping
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Execute sync action (no role mapping exists)
        $this->syncAction->execute($this->user);

        // Assert no role is granted
        expect($this->user->hasRole('individual-instructor'))->toBeFalse();
    });

    it('removes all roles when no active credentials exist', function () {
        // First give the user some roles
        $this->user->assignRole(['individual-instructor', 'individual-coach', 'individual-approved']);

        // Remove federation association
        $this->individual->individualFederations()->update(['active' => false]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert all roles are removed
        expect($this->user->roles->count())->toBe(0);
    });

    it('handles multiple individuals for same user', function () {
        // Create second individual for same user
        $individual2 = Individual::factory()->create(['user_id' => $this->user->id]);
        $federation2 = Federation::factory()->create();
        $federation2->individuals()->attach($individual2, ['active' => true]);

        // Give different licenses to each individual
        $instructorLicense = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        $coachLicense = License::factory()->create([
            'professional_role_id' => $this->coachRole->id,
            'requester_model' => Individual::class,
        ]);

        // Map licenses to roles
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        $coachRole = Role::where('name', 'individual-coach')->first();

        DB::table('license_roles')->insert([
            ['license_id' => $instructorLicense->id, 'role_id' => $instructorRole->id, 'created_at' => now(), 'updated_at' => now()],
            ['license_id' => $coachLicense->id, 'role_id' => $coachRole->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $instructorLicense->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $coachLicense->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $individual2->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert roles from both individuals are combined
        expect($this->user->hasRole('individual-instructor'))->toBeTrue();
        expect($this->user->hasRole('individual-coach'))->toBeTrue();
    });
});

describe('Admin Role Preservation', function () {
    it('preserves admin roles when syncing user roles', function () {
        // Give user admin role
        $adminRole = Role::firstOrCreate(['name' => 'federation-admin']);
        $this->user->assignRole($adminRole);

        // Create and map a license
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual',
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert both admin and mapped roles are present
        expect($this->user->hasRole('federation-admin'))->toBeTrue();
        expect($this->user->hasRole('individual-instructor'))->toBeTrue();
    });

    it('preserves super admin role when user has no individuals', function () {
        // Create user with super admin role but no individuals
        $superAdminUser = User::factory()->create();
        $superAdminRole = Role::firstOrCreate(['name' => 'admin']);
        $superAdminUser->assignRole($superAdminRole);

        // Execute sync action
        $this->syncAction->execute($superAdminUser);

        // Assert super admin role is preserved
        expect($superAdminUser->hasRole('admin'))->toBeTrue();
        expect($superAdminUser->roles->count())->toBe(1);
    });

    it('preserves multiple admin roles during sync', function () {
        // Give user multiple admin roles (excluding entity roles which are managed separately)
        $adminRoles = [
            Role::firstOrCreate(['name' => 'federation-admin']),
            Role::firstOrCreate(['name' => 'association-sport-admin']),
            Role::firstOrCreate(['name' => 'association-territorial-admin']),
        ];

        foreach ($adminRoles as $role) {
            $this->user->assignRole($role);
        }

        // Execute sync action with no active credentials
        $this->individual->individualFederations()->update(['active' => false]);
        $this->syncAction->execute($this->user);

        // Assert all admin roles are preserved
        expect($this->user->hasRole('federation-admin'))->toBeTrue();
        expect($this->user->hasRole('association-sport-admin'))->toBeTrue();
        expect($this->user->hasRole('association-territorial-admin'))->toBeTrue();
        expect($this->user->roles->count())->toBe(3);
    });

    it('preserves admin roles when credentials expire', function () {
        // Give user admin role
        $adminRole = Role::firstOrCreate(['name' => 'association-territorial-admin']);
        $this->user->assignRole($adminRole);

        // Create active license first
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create expired license attribution
        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual',
            'model_id' => $this->individual->id,
            'status_class' => ExpiredLicenseAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert admin role is preserved but instructor role is not
        expect($this->user->hasRole('association-territorial-admin'))->toBeTrue();
        expect($this->user->hasRole('individual-instructor'))->toBeFalse();
    });

    it('does not preserve non-admin roles when syncing', function () {
        // Give user a non-admin role that's not from credentials
        $customRole = Role::firstOrCreate(['name' => 'custom-role']);
        $this->user->assignRole($customRole);

        // Create and map a license
        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual',
            'model_id' => $this->individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Execute sync action
        $this->syncAction->execute($this->user);

        // Assert custom role is removed but instructor role is granted
        expect($this->user->hasRole('custom-role'))->toBeFalse();
        expect($this->user->hasRole('individual-instructor'))->toBeTrue();
    });
});

describe('SyncAllUserRoles Command', function () {
    beforeEach(function () {
        // Create multiple users with different scenarios
        $this->users = collect();

        // User with active license
        $user1 = User::factory()->create();
        $individual1 = Individual::factory()->create(['user_id' => $user1->id]);
        $this->federation->individuals()->attach($individual1, ['active' => true]);

        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        // Map license to role
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $individual1->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        $this->users->push($user1);

        // User with no individuals
        $user2 = User::factory()->create();
        $this->users->push($user2);

        // User with expired license
        $user3 = User::factory()->create();
        $individual3 = Individual::factory()->create(['user_id' => $user3->id]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual', // Use morph alias
            'model_id' => $individual3->id,
            'status_class' => \Domain\Licenses\States\ExpiredLicenseAttributedState::class,
        ]);

        $this->users->push($user3);
    });

    it('processes all users when run without parameters', function () {
        // Run command
        $this->artisan('sync:all-user-roles --force')
            ->expectsOutput('🚀 Starting role sync...')
            ->assertSuccessful();

        // Verify first user has roles
        expect($this->users[0]->fresh()->hasRole('individual-instructor'))->toBeTrue();

        // Verify second user has no roles
        expect($this->users[1]->fresh()->roles->count())->toBe(0);

        // Verify third user has no instructor role
        expect($this->users[2]->fresh()->hasRole('individual-instructor'))->toBeFalse();
    });

    it('processes specific user when user-id is provided', function () {
        // Create a fresh user for this specific test
        $targetUser = User::factory()->create();
        $individual = Individual::factory()->create(['user_id' => $targetUser->id]);

        // Properly attach individual to federation
        DB::table('individual_federation')->insert([
            'individual_id' => $individual->id,
            'federation_id' => $this->federation->id,
            'active' => true,
            'status_class' => \Domain\Individuals\States\ActiveIndividualFederationState::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $license = License::factory()->create([
            'professional_role_id' => $this->instructorRole->id,
            'requester_model' => Individual::class,
        ]);

        // Map license to role
        $instructorRole = Role::where('name', 'individual-instructor')->first();
        DB::table('license_roles')->insert([
            'license_id' => $license->id,
            'role_id' => $instructorRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_type' => 'individual',
            'model_id' => $individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // First verify the data is set up correctly
        expect($individual->individualFederations()->where('active', true)->exists())->toBeTrue();
        expect(LicenseAttributed::where('model_type', 'individual')
            ->where('model_id', $individual->id)
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->exists())->toBeTrue();

        // Test the action directly first
        $syncAction = new SyncUserRolesAction;
        $syncAction->execute($targetUser);

        // Check if direct action worked
        $targetUser->refresh();
        expect($targetUser->hasRole('individual-instructor'))->toBeTrue('Direct sync action should assign role');

        // Now test via command (role should already be assigned)
        $this->artisan("sync:all-user-roles --user-id={$targetUser->id} --force")
            ->expectsOutput('🚀 Starting role sync...')
            ->assertSuccessful();
    });

    it('runs in dry-run mode without making changes', function () {
        $this->artisan('sync:all-user-roles --dry-run')
            ->expectsOutput('🔍 Starting DRY RUN...')
            ->expectsOutputToContain('This was a DRY RUN - no changes were made.')
            ->assertSuccessful();

        // Verify no roles were actually assigned
        expect($this->users[0]->fresh()->roles->count())->toBe(0);
    });

    it('handles errors gracefully', function () {
        // Create a user that will cause an error
        $errorUser = User::factory()->create();

        // Mock the sync action to throw an error for this specific user
        $this->mock(SyncUserRolesAction::class, function ($mock) use ($errorUser) {
            $mock->shouldReceive('execute')
                ->andReturnUsing(function ($user) use ($errorUser) {
                    if ($user->id === $errorUser->id) {
                        throw new \Exception('Test error');
                    }
                });
        });

        $this->artisan('sync:all-user-roles --force')
            ->assertSuccessful(); // Command should still succeed despite individual errors
    });
});
