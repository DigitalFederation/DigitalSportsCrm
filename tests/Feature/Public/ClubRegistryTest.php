<?php

use App\Livewire\Public\ClubRegistry;
use App\Models\Committee;
use Domain\Entities\Models\Entity;
use Domain\Geographic\Models\District;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Livewire\Livewire;

beforeEach(function () {
    $this->sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $this->entityType = LicenseType::firstOrCreate(
        ['name' => 'entity'],
        ['is_individual' => false]
    );

    $this->license = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'type_id' => $this->entityType->id,
        'name' => 'Sport Club License',
    ]);
});

test('club registry page can be rendered', function () {
    $response = $this->get(route('public.club-registry'));

    $response->assertStatus(200);
    $response->assertSeeLivewire(ClubRegistry::class);
});

test('it displays entities with active SPORT entity licenses', function () {
    $entity = Entity::factory()->create([
        'name' => 'TestSportClub',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->assertSee('TestSportClub');
});

test('it displays entities with expired SPORT entity licenses', function () {
    $entity = Entity::factory()->create([
        'name' => 'ExpiredClub',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->assertSee('ExpiredClub');
});

test('it displays entities with suspended SPORT entity licenses', function () {
    $entity = Entity::factory()->create([
        'name' => 'SuspendedClub',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $this->license->id,
        'status_class' => SuspendedLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->assertSee('SuspendedClub');
});

test('it filters entities by name', function () {
    $club1 = Entity::factory()->create(['name' => 'AquaClub']);
    $club2 = Entity::factory()->create(['name' => 'OtherEntity']);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $club1->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $club2->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->set('searchName', 'Aqua')
        ->assertSee('AquaClub')
        ->assertDontSee('OtherEntity');
});

test('it filters entities by district', function () {
    $district = District::factory()->create(['is_active' => true]);
    $otherDistrict = District::factory()->create(['is_active' => true]);

    $club1 = Entity::factory()->create([
        'name' => 'LocalClub',
        'district_id' => $district->id,
    ]);

    $club2 = Entity::factory()->create([
        'name' => 'FarClub',
        'district_id' => $otherDistrict->id,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $club1->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $club2->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->set('selectedDistrict', $district->id)
        ->assertSee('LocalClub')
        ->assertDontSee('FarClub');
});

test('it filters entities by license status', function () {
    $activeEntity = Entity::factory()->create(['name' => 'ActiveClubEntity']);
    $expiredEntity = Entity::factory()->create(['name' => 'ExpiredClubEntity']);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $activeEntity->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $expiredEntity->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->set('selectedStatus', 'active')
        ->assertSee('ActiveClubEntity')
        ->assertDontSee('ExpiredClubEntity');

    Livewire::test(ClubRegistry::class)
        ->set('selectedStatus', 'expired')
        ->assertSee('ExpiredClubEntity')
        ->assertDontSee('ActiveClubEntity');
});

test('it filters entities by sport license', function () {
    $secondLicense = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'type_id' => $this->entityType->id,
        'name' => 'Second Sport License',
    ]);

    $entity1 = Entity::factory()->create(['name' => 'HockeyClubEntity']);
    $entity2 = Entity::factory()->create(['name' => 'FishingClubEntity']);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity1->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity2->id,
        'license_id' => $secondLicense->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->set('selectedSportLicense', (string) $this->license->id)
        ->assertSee('HockeyClubEntity')
        ->assertDontSee('FishingClubEntity');

    Livewire::test(ClubRegistry::class)
        ->set('selectedSportLicense', (string) $secondLicense->id)
        ->assertSee('FishingClubEntity')
        ->assertDontSee('HockeyClubEntity');
});

test('it can clear all filters', function () {
    $district = District::factory()->create(['is_active' => true]);

    Livewire::test(ClubRegistry::class)
        ->set('searchName', 'Test')
        ->set('selectedSportLicense', (string) $this->license->id)
        ->set('selectedDistrict', (string) $district->id)
        ->set('selectedStatus', 'active')
        ->call('clearFilters')
        ->assertSet('searchName', '')
        ->assertSet('selectedSportLicense', '')
        ->assertSet('selectedDistrict', '')
        ->assertSet('selectedStatus', '');
});

test('it displays entities with active validation plan affiliation but no SPORT license', function () {
    $federation = \Domain\Federations\Models\Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    $affiliationPlan = \Domain\Memberships\Models\AffiliationPlan::factory()->validation()->create([
        'federation_id' => $federation->id,
    ]);

    $membershipPackage = \Domain\Memberships\Models\MembershipPackage::factory()->entity()->create();
    $membershipPackage->affiliationPlans()->attach($affiliationPlan->id);

    $entity = Entity::factory()->create(['name' => 'ValidationPlanClub']);

    $memberSubscription = \Domain\Memberships\Models\MemberSubscription::factory()->forEntity($entity)->create([
        'membership_package_id' => $membershipPackage->id,
    ]);

    \Domain\Memberships\Models\Affiliation::factory()->forEntity($entity)->create([
        'federation_id' => $federation->id,
        'member_subscription_id' => $memberSubscription->id,
        'status_class' => \Domain\Memberships\States\ActiveAffiliationState::class,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
    ]);

    Livewire::test(ClubRegistry::class)
        ->assertSee('ValidationPlanClub');
});

test('it does not display entities without SPORT license or validation plan affiliation', function () {
    $entity = Entity::factory()->create(['name' => 'InvisibleClub']);

    Livewire::test(ClubRegistry::class)
        ->assertDontSee('InvisibleClub');
});

test('it does not display entities with DIVINGSERVICES licenses', function () {
    $divingCommittee = Committee::factory()->create(['code' => 'DIVINGSERVICES']);
    $divingLicense = License::factory()->create([
        'committee_id' => $divingCommittee->id,
        'type_id' => $this->entityType->id,
    ]);

    $entity = Entity::factory()->create(['name' => 'DivingOnlyEntity']);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $divingLicense->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->assertDontSee('DivingOnlyEntity');
});
