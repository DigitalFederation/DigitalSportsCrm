<?php

use App\Livewire\Public\DivingServiceProviderRegistry;
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
    $this->divingServicesCommittee = Committee::firstOrCreate(
        ['code' => 'DIVINGSERVICES'],
        ['name' => 'Diving Services', 'is_international' => false]
    );

    $this->entityType = LicenseType::firstOrCreate(
        ['name' => 'entity'],
        ['is_individual' => false]
    );

    $this->license = License::factory()->create([
        'committee_id' => $this->divingServicesCommittee->id,
        'type_id' => $this->entityType->id,
        'name' => 'Diving Service Provider License',
    ]);
});

test('diving service provider registry page can be rendered', function () {
    $response = $this->get(route('public.diving-service-providers'));

    $response->assertStatus(200);
    $response->assertSeeLivewire(DivingServiceProviderRegistry::class);
});

test('it displays entities with active DIVINGSERVICES entity licenses', function () {
    $entity = Entity::factory()->create([
        'name' => 'TestDivingCenter',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->assertSee('TestDivingCenter');
});

test('it displays entities with expired DIVINGSERVICES entity licenses', function () {
    $entity = Entity::factory()->create([
        'name' => 'ExpiredDivingCenter',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->assertSee('ExpiredDivingCenter');
});

test('it displays entities with suspended DIVINGSERVICES entity licenses', function () {
    $entity = Entity::factory()->create([
        'name' => 'SuspendedDivingCenter',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $this->license->id,
        'status_class' => SuspendedLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->assertSee('SuspendedDivingCenter');
});

test('it filters entities by name', function () {
    $center1 = Entity::factory()->create(['name' => 'OceanDiveCenter']);
    $center2 = Entity::factory()->create(['name' => 'OtherProvider']);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $center1->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $center2->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->set('searchName', 'Ocean')
        ->assertSee('OceanDiveCenter')
        ->assertDontSee('OtherProvider');
});

test('it filters entities by district', function () {
    $district = District::factory()->create(['is_active' => true]);
    $otherDistrict = District::factory()->create(['is_active' => true]);

    $center1 = Entity::factory()->create([
        'name' => 'LocalDiveCenter',
        'district_id' => $district->id,
    ]);

    $center2 = Entity::factory()->create([
        'name' => 'FarDiveCenter',
        'district_id' => $otherDistrict->id,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $center1->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $center2->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->set('selectedDistrict', $district->id)
        ->assertSee('LocalDiveCenter')
        ->assertDontSee('FarDiveCenter');
});

test('it filters entities by license status', function () {
    $activeEntity = Entity::factory()->create(['name' => 'ActiveDiveProvider']);
    $expiredEntity = Entity::factory()->create(['name' => 'ExpiredDiveProvider']);

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

    Livewire::test(DivingServiceProviderRegistry::class)
        ->set('selectedStatus', 'active')
        ->assertSee('ActiveDiveProvider')
        ->assertDontSee('ExpiredDiveProvider');

    Livewire::test(DivingServiceProviderRegistry::class)
        ->set('selectedStatus', 'expired')
        ->assertSee('ExpiredDiveProvider')
        ->assertDontSee('ActiveDiveProvider');
});

test('it can clear all filters', function () {
    $district = District::factory()->create(['is_active' => true]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->set('searchName', 'Test')
        ->set('selectedDistrict', $district->id)
        ->set('selectedStatus', 'active')
        ->call('clearFilters')
        ->assertSet('searchName', '')
        ->assertSet('selectedDistrict', '')
        ->assertSet('selectedStatus', '');
});

test('it does not display entities with SPORT licenses', function () {
    $sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );
    $sportLicense = License::factory()->create([
        'committee_id' => $sportCommittee->id,
        'type_id' => $this->entityType->id,
    ]);

    $entity = Entity::factory()->create(['name' => 'SportOnlyEntity']);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $sportLicense->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->assertDontSee('SportOnlyEntity');
});
