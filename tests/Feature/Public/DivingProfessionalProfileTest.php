<?php

use App\Livewire\Public\DivingProfessionalProfile;
use App\Livewire\Public\DivingProfessionals;
use App\Models\Committee;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Livewire\Livewire;

beforeEach(function () {
    $this->divingServicesCommittee = Committee::firstOrCreate(
        ['code' => 'DIVINGSERVICES'],
        ['name' => 'Diving Services', 'is_international' => false]
    );

    $this->license = License::factory()->create([
        'committee_id' => $this->divingServicesCommittee->id,
        'name' => 'Diving Instructor License',
    ]);
});

test('diving professional profile page renders for visible professionals', function () {
    $individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'ProfileDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $response = $this->get(route('public.diving-professional-profile', $individual));

    $response->assertSuccessful();
    $response->assertSeeLivewire(DivingProfessionalProfile::class);
});

test('diving professional profile page returns 404 for hidden professionals', function () {
    $individual = Individual::factory()->create([
        'visible_in_diving_professional_registry' => false,
    ]);

    $response = $this->get(route('public.diving-professional-profile', $individual));

    $response->assertNotFound();
});

test('diving professional profile page shows correct data', function () {
    $district = District::factory()->create(['is_active' => true, 'name' => 'Porto']);

    $individual = Individual::factory()->create([
        'name' => 'Maria',
        'surname' => 'TestDiver',
        'district_id' => $district->id,
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionalProfile::class, ['individual' => $individual])
        ->assertSee('Maria')
        ->assertSee('TestDiver')
        ->assertSee('Porto');
});

test('diving professional profile page shows license status badges', function () {
    $individual = Individual::factory()->create([
        'name' => 'Paulo',
        'surname' => 'BadgeDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionalProfile::class, ['individual' => $individual])
        ->assertSee('Paulo')
        ->assertSee('BadgeDiver');
});

test('diving professional profile has no events section', function () {
    $individual = Individual::factory()->create([
        'name' => 'Elena',
        'surname' => 'NoEventsDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    Livewire::test(DivingProfessionalProfile::class, ['individual' => $individual])
        ->assertDontSee(__('public.coach_registry.profile.competitions'))
        ->assertDontSee(__('public.technical_official_registry.profile.competitions'));
});

test('diving professional registry table renders with view profile links', function () {
    $individual = Individual::factory()->create([
        'name' => 'Ana',
        'surname' => 'TableDiver',
        'visible_in_diving_professional_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->assertSee('Ana')
        ->assertSee('TableDiver')
        ->assertSeeHtml(route('public.diving-professional-profile', $individual));
});
