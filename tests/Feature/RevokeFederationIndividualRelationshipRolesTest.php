<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\AssociateIndividualToFederationAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Users\Actions\SyncUserRolesAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('User roles are updated correctly upon individual federation detachment', function () {
    $committee_scientific = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Technical Committee']);
    $committee_diving = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving Committee']);

    $professionalRoleDiving = ProfessionalRole::factory()->create(['name' => 'Diver', 'role' => 'DIVER', 'committee_id' => $committee_diving->id]);
    $professionalRoleScientific = ProfessionalRole::factory()->create(['name' => 'Scientific Instructor', 'role' => 'INSTRUCTOR', 'committee_id' => $committee_scientific->id]);

    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $group_federation = Group::factory()->create(['code' => 'FEDERATION']);

    $user_individual = User::factory()->create(['group_id' => $group_individual->id]);
    $individual = Individual::factory()->create(['user_id' => $user_individual->id]);
    $individual->professionalRoles()->attach($professionalRoleDiving->id);

    $federationOne = Federation::factory()->create(['is_local' => false]);
    $federationTwo = Federation::factory()->create(['is_local' => false]);

    $associateAction = new AssociateIndividualToFederationAction;
    $associateAction($individual, $federationOne);
    $associateAction($individual, $federationTwo);

    // Get the required roles
    $roleDiver = \Spatie\Permission\Models\Role::where('name', 'individual-diver')->first();
    $roleScientificInstructor = \Spatie\Permission\Models\Role::where('name', 'view-individual-scientific-instructor')->first();

    $scientificCertification = Certification::factory()->create(['committee_id' => $committee_scientific->id, 'professional_role_id' => $professionalRoleScientific->id]);
    $divingCertification = Certification::factory()->create(['committee_id' => $committee_diving->id, 'professional_role_id' => $professionalRoleDiving->id]);

    // Create the certification_roles mappings
    \DB::table('certification_roles')->insert([
        [
            'certification_id' => $scientificCertification->id,
            'role_id' => $roleScientificInstructor->id,
            'committee_id' => $committee_scientific->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'certification_id' => $divingCertification->id,
            'role_id' => $roleDiver->id,
            'committee_id' => $committee_diving->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $scientificCertificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $scientificCertification->id,
        'federation_id' => $federationOne->id,
        'individual_id' => $individual->id,
        'status_class' => \Domain\Certifications\States\ActiveCertificationAttributedState::class,
    ]);

    $divingCertificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $divingCertification->id,
        'federation_id' => $federationTwo->id,
        'individual_id' => $individual->id,
        'status_class' => \Domain\Certifications\States\ActiveCertificationAttributedState::class,
    ]);

    // Execute the SyncRoles action
    $syncRolesAction = new SyncUserRolesAction;
    $syncRolesAction->execute($user_individual);

    expect($user_individual->getRoleNames())->toHaveCount(2)->toContain('individual-diver')->toContain('view-individual-scientific-instructor');

    // Change the state of the certification attributed to suspended
    $individual->federations()->detach($federationTwo->id);

    // Sync roles again
    $syncRolesAction->execute($user_individual);

    expect($user_individual->getRoleNames())->toHaveCount(2)->toContain('individual-diver')->toContain('view-individual-scientific-instructor');

});
