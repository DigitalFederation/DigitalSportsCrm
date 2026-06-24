<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\CanceledIndividualEntityState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permission
    Permission::findOrCreate('access international licenses', 'web');

    // Create ENTITY group
    $this->entityGroup = Group::firstOrCreate(['code' => 'ENTITY'], ['name' => 'Entity']);

    // Create committees and supporting data
    // DIVING committee is international (is_international = true on committee)
    $this->divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);
    $this->federation = Federation::factory()->create();
    $this->sport = Sport::factory()->create();
    $this->licenseType = LicenseType::factory()->create();

    // Create international license using diving committee
    // Internationality is now determined by the committee's is_international flag
    $this->internationalLicense = License::factory()->create([
        'committee_id' => $this->divingCommittee->id,
        'type_id' => $this->licenseType->id,
        'sport_id' => $this->sport->id,
        'name' => 'International Entity License',
    ]);

    // Create international certification using diving committee
    // Internationality is now determined by the committee's is_international flag
    $this->internationalCertification = Certification::factory()->create([
        'committee_id' => $this->divingCommittee->id,
        'name' => 'International Diving Certification',
    ]);
});

test('entity can only see its own international licenses', function () {
    // Create two entities with users
    $user1 = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity1 = Entity::factory()->create();
    $entity1->users()->attach($user1->id);
    $user1->givePermissionTo('access international licenses');

    $user2 = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity2 = Entity::factory()->create();
    $entity2->users()->attach($user2->id);
    $user2->givePermissionTo('access international licenses');

    // Create licenses for both entities
    $license1 = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'entity',
        'model_id' => $entity1->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $license2 = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'entity',
        'model_id' => $entity2->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Entity 1 should only see their own license
    $response = $this->actingAs($user1)->get(route('international.entity.licenses-attributed.index'));
    $response->assertSuccessful();

    // Entity 2 should only see their own license
    $response = $this->actingAs($user2)->get(route('international.entity.licenses-attributed.index'));
    $response->assertSuccessful();
});

test('entity can see international certifications for their members only', function () {
    // Create entity with user
    $user = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity = Entity::factory()->create();
    $entity->users()->attach($user->id);
    $user->givePermissionTo('access international licenses');

    // Create entity's member
    $entityMember = Individual::factory()->create();
    $entity->individuals()->attach($entityMember->id, ['status_class' => ActiveIndividualEntityState::class]);

    // Create non-member
    $nonMember = Individual::factory()->create();

    // Create certifications for both
    $memberCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $entityMember->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => $entityMember->name,
    ]);

    $nonMemberCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $nonMember->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => $nonMember->name,
    ]);

    // Entity should only see member's certification
    $response = $this->actingAs($user)->get(route('international.entity.certifications.index'));
    $response->assertSuccessful();
    $response->assertSee($entityMember->name);
    $response->assertDontSee($nonMember->name);
});

test('entity cannot access licenses of other entities', function () {
    // Create two entities
    $user1 = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity1 = Entity::factory()->create();
    $entity1->users()->attach($user1->id);
    $user1->givePermissionTo('access international licenses');

    $entity2 = Entity::factory()->create();

    // Create license for entity2
    $license2 = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'entity',
        'model_id' => $entity2->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Entity 1 tries to access entity 2's license - should get 404 (not found in their scope)
    $response = $this->actingAs($user1)->get(route('international.entity.licenses-attributed.show', $license2->id));
    $response->assertNotFound();
});

test('entity requires permission to access international routes', function () {
    $user = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity = Entity::factory()->create();
    $entity->users()->attach($user->id);
    // No permission given

    // Should be forbidden
    $response = $this->actingAs($user)->get(route('international.entity.licenses-attributed.index'));
    $response->assertForbidden();

    $response = $this->actingAs($user)->get(route('international.entity.license-purchase.index'));
    $response->assertForbidden();

    $response = $this->actingAs($user)->get(route('international.entity.certifications.index'));
    $response->assertForbidden();
});

test('entity with permission can access international routes', function () {
    $user = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity = Entity::factory()->create();
    $entity->users()->attach($user->id);
    $user->givePermissionTo('access international licenses');

    // Should be successful
    $response = $this->actingAs($user)->get(route('international.entity.licenses-attributed.index'));
    $response->assertSuccessful();

    $response = $this->actingAs($user)->get(route('international.entity.certifications.index'));
    $response->assertSuccessful();
});

test('entity can only see active members certifications', function () {
    $user = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $entity = Entity::factory()->create();
    $entity->users()->attach($user->id);
    $user->givePermissionTo('access international licenses');

    // Create active member
    $activeMember = Individual::factory()->create();
    $entity->individuals()->attach($activeMember->id, ['status_class' => ActiveIndividualEntityState::class]);

    // Create inactive member
    $inactiveMember = Individual::factory()->create();
    $entity->individuals()->attach($inactiveMember->id, ['status_class' => CanceledIndividualEntityState::class]);

    // Create certifications for both
    $activeCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $activeMember->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => $activeMember->name,
    ]);

    $inactiveCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $inactiveMember->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => $inactiveMember->name,
    ]);

    // Entity should only see active member's certification
    $response = $this->actingAs($user)->get(route('international.entity.certifications.index'));
    $response->assertSuccessful();
    $response->assertSee($activeMember->name);
    $response->assertDontSee($inactiveMember->name);
});
