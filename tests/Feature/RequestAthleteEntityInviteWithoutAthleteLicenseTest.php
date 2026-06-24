<?php

use App\Models\Committee;
use App\Models\Group;
use Domain\Entities\DataTransferObject\EntityAthleteData;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\AssociateAthleteToEntityAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('cant request a invite for athlete if doesnt have a athlete license', function () {
    $committee = Committee::factory()->create(['code' => 'SPORTS', 'name' => 'Sports Committee']);
    $group_federation = Group::factory()->create(['code' => 'FEDERATION']);
    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Users
    $user_federation = \App\Models\User::factory()->create([
        'group_id' => $group_federation->id,
    ]);
    $user_individual = \App\Models\User::factory()->create([
        'group_id' => $group_individual->id,
    ]);

    $professionalRoleAthlete = ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $professionalRoleCoach = ProfessionalRole::factory()->create(['role' => 'COACH']);

    // Federation
    $federation = Federation::factory()->create(['is_local' => false]);
    $federation->users()->attach($user_federation);

    // Entity
    $entity = Entity::factory()->create();
    $federation->entities()->attach($entity);

    // Individual
    $individual = Individual::factory()->create(['user_id' => $user_individual, 'name' => 'Zeca']);
    $federation->individuals()->attach($individual);
    $entity->individuals()->attach($individual, ['status_class' => ActiveIndividualEntityState::class]);

    // License
    $licenseCoach = License::factory()->create([
        'name' => 'Finswimming Sport Coach License',
        'professional_role_id' => $professionalRoleCoach->id,
    ]);
    $licenseAthlete = License::factory()->create([
        'name' => 'Finswimming Sport License',
        'professional_role_id' => $professionalRoleAthlete->id,
    ]);

    // Add the license to the MembershipPlan
    $membershipPlan = MembershipPlan::factory()->create(['committee_id' => $committee->id]);
    $membershipPlan->licenses()->attach($licenseAthlete->id);
    $membership = Membership::factory()->create(['federation_id' => $federation->id]);
    $membership->plans()->attach($membershipPlan->id);

    // Give individual the license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'federation_id' => $federation->id,
        'license_id' => $licenseCoach->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Create the Invitation
    $this->actingAs($user_individual);
    // Set the expected exception
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('The individual Zeca is missing the required license to be invited');

    $associateAthleteToEntityAction = new AssociateAthleteToEntityAction;
    $associateAthleteToEntityAction(EntityAthleteData::fromArray([
        'entity_id' => $entity->id,
        'individual_id' => $individual->id,
        'sport_id' => $licenseCoach->sport_id,
        'entity_name' => $entity->name,
        'individual_name' => $individual->name,
        'sport_name' => $licenseCoach->name,
    ]));

});

it('can request a invite for athlete if it has a athlete license', function () {
    $committee = Committee::factory()->create(['code' => 'SPORTS', 'name' => 'Sports Committee']);
    $group_federation = Group::factory()->create(['code' => 'FEDERATION']);
    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Users
    $user_federation = \App\Models\User::factory()->create([
        'group_id' => $group_federation->id,
    ]);
    $user_individual = \App\Models\User::factory()->create([
        'group_id' => $group_individual->id,
    ]);

    $professionalRoleAthlete = ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $professionalRoleCoach = ProfessionalRole::factory()->create(['role' => 'COACH']);

    // Federation
    $federation = Federation::factory()->create(['is_local' => false]);
    $federation->users()->attach($user_federation);

    // Entity
    $entity = Entity::factory()->create();
    $federation->entities()->attach($entity);

    // Individual
    $individual = Individual::factory()->create(['user_id' => $user_individual, 'name' => 'Zeca']);
    $federation->individuals()->attach($individual);
    $entity->individuals()->attach($individual, ['status_class' => ActiveIndividualEntityState::class]);

    // License
    $licenseCoach = License::factory()->create([
        'name' => 'Finswimming Sport Coach License',
        'professional_role_id' => $professionalRoleCoach->id,
    ]);
    $licenseAthlete = License::factory()->create([
        'name' => 'Finswimming Sport License',
        'professional_role_id' => $professionalRoleAthlete->id,
    ]);

    // Add the license to the MembershipPlan
    $membershipPlan = MembershipPlan::factory()->create(['committee_id' => $committee->id]);
    $membershipPlan->licenses()->attach($licenseAthlete->id);
    $membership = Membership::factory()->create(['federation_id' => $federation->id]);
    $membership->plans()->attach($membershipPlan->id);

    // Give individual the license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'federation_id' => $federation->id,
        'license_id' => $licenseAthlete->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Create the Invitation
    $this->actingAs($user_individual);
    $associateAthleteToEntityAction = new AssociateAthleteToEntityAction;
    $entityAthlete = $associateAthleteToEntityAction(EntityAthleteData::fromArray([
        'entity_id' => $entity->id,
        'individual_id' => $individual->id,
        'sport_id' => $licenseAthlete->sport_id,
        'entity_name' => $entity->name,
        'individual_name' => $individual->name,
        'sport_name' => $licenseAthlete->name,
    ]));

    // Assert Object State
    expect($entityAthlete)->toBeInstanceOf(EntityAthlete::class);
    expect($entityAthlete->individual_id)->toEqual($individual->id);

});
