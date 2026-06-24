<?php

use App\Livewire\Public\DivingProfessionals;
use App\Models\Committee;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Livewire\Livewire;

beforeEach(function () {
    // Create the DIVINGSERVICES committee if it doesn't exist
    $this->committee = Committee::firstOrCreate(
        ['code' => 'DIVINGSERVICES'],
        ['name' => 'Diving Services', 'is_international' => false]
    );

    // Create a license with DIVINGSERVICES committee
    $this->license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'Diving Professional License',
    ]);
});

test('diving professionals page can be rendered', function () {
    $response = $this->get(route('public.diving-professionals'));

    $response->assertStatus(200);
    $response->assertSeeLivewire(DivingProfessionals::class);
});

test('it displays professionals with active DIVINGSERVICES licenses', function () {
    $individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'Diver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->assertSee('John')
        ->assertSee('Diver');
});

test('it displays professionals with expired DIVINGSERVICES licenses', function () {
    $individual = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'ExpiredDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->assertSee('Jane')
        ->assertSee('ExpiredDiver');
});

test('it displays professionals with suspended DIVINGSERVICES licenses', function () {
    $individual = Individual::factory()->create([
        'name' => 'Mike',
        'surname' => 'SuspendedDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => SuspendedLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->assertSee('Mike')
        ->assertSee('SuspendedDiver');
});

test('it filters professionals by name', function () {
    $john = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'TestDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    $jane = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'OtherDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $john->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $jane->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->set('searchName', 'John')
        ->assertSee('John')
        ->assertSee('TestDiver')
        ->assertDontSee('OtherDiver');
});

test('it filters professionals by district', function () {
    $district = District::factory()->create(['is_active' => true]);
    $otherDistrict = District::factory()->create(['is_active' => true]);

    $johnInDistrict = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'LocalDiver',
        'district_id' => $district->id,
        'visible_in_diving_professional_registry' => true,
    ]);

    $janeInOtherDistrict = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'FarDiver',
        'district_id' => $otherDistrict->id,
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $johnInDistrict->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $janeInOtherDistrict->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->set('selectedDistrict', $district->id)
        ->assertSee('LocalDiver')
        ->assertDontSee('FarDiver');
});

test('it filters professionals by license status', function () {
    $activeIndividual = Individual::factory()->create([
        'name' => 'Active',
        'surname' => 'Diver',
        'visible_in_diving_professional_registry' => true,
    ]);

    $expiredIndividual = Individual::factory()->create([
        'name' => 'Expired',
        'surname' => 'Diver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $activeIndividual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $expiredIndividual->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    // Filter by active only
    Livewire::test(DivingProfessionals::class)
        ->set('selectedStatus', 'active')
        ->assertSee('Active')
        ->assertDontSee('Expired');

    // Filter by expired only
    Livewire::test(DivingProfessionals::class)
        ->set('selectedStatus', 'expired')
        ->assertSee('Expired')
        ->assertDontSee('Active');
});

test('it can clear all filters', function () {
    $district = District::factory()->create(['is_active' => true]);

    Livewire::test(DivingProfessionals::class)
        ->set('searchName', 'John')
        ->set('selectedDistrict', $district->id)
        ->set('selectedStatus', 'active')
        ->call('clearFilters')
        ->assertSet('searchName', '')
        ->assertSet('selectedDistrict', '')
        ->assertSet('selectedStatus', '');
});

test('it does not display individuals without DIVINGSERVICES licenses', function () {
    // Create a different committee
    $otherCommittee = Committee::factory()->create(['code' => 'OTHER']);
    $otherLicense = License::factory()->create([
        'committee_id' => $otherCommittee->id,
    ]);

    $individualWithOtherLicense = Individual::factory()->create([
        'name' => 'NotA',
        'surname' => 'DivingProfessional',
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individualWithOtherLicense->id,
        'license_id' => $otherLicense->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->assertDontSee('NotA')
        ->assertDontSee('DivingProfessional');
});
