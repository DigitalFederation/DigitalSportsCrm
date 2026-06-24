<?php

use App\Livewire\Profile\UpdateRegistryPrivacyForm;
use App\Livewire\Public\CoachRegistry;
use App\Livewire\Public\DivingProfessionals;
use App\Livewire\Public\TechnicalOfficialRegistry;
use App\Models\Committee;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Livewire\Livewire;

test('registry visibility columns default to true', function () {
    $individual = Individual::factory()->create();
    $individual->refresh();

    expect($individual->visible_in_coach_registry)->toBeTrue()
        ->and($individual->visible_in_technical_official_registry)->toBeTrue()
        ->and($individual->visible_in_diving_professional_registry)->toBeTrue();
});

test('registry visibility columns can be set to true', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => true,
        'visible_in_technical_official_registry' => true,
        'visible_in_diving_professional_registry' => true,
    ]);

    expect($individual->visible_in_coach_registry)->toBeTrue()
        ->and($individual->visible_in_technical_official_registry)->toBeTrue()
        ->and($individual->visible_in_diving_professional_registry)->toBeTrue();
});

test('coach registry only shows individuals with visible_in_coach_registry enabled', function () {
    $sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $coachRole = ProfessionalRole::firstOrCreate(
        ['role' => 'COACH', 'code' => 'PRIVCOACH'],
        ['name' => 'Privacy Test Coach', 'committee_id' => $sportCommittee->id]
    );

    $license = License::factory()->create([
        'committee_id' => $sportCommittee->id,
        'professional_role_id' => $coachRole->id,
    ]);
    $certification = Certification::factory()->create([
        'committee_id' => $sportCommittee->id,
        'professional_role_id' => $coachRole->id,
        'license_id' => $license->id,
    ]);

    $visibleCoach = Individual::factory()->create([
        'name' => 'VisibleCoach',
        'surname' => 'Person',
        'visible_in_coach_registry' => true,
    ]);

    $hiddenCoach = Individual::factory()->create([
        'name' => 'HiddenCoach',
        'surname' => 'Person',
        'visible_in_coach_registry' => false,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $visibleCoach->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $visibleCoach->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $hiddenCoach->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $hiddenCoach->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('VisibleCoach')
        ->assertDontSee('HiddenCoach');
});

test('technical official registry only shows individuals with visible_in_technical_official_registry enabled', function () {
    $sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $techRole = ProfessionalRole::firstOrCreate(
        ['role' => 'TECHNICAL_OFFICIAL', 'code' => 'PRIVTECH'],
        ['name' => 'Privacy Test Tech Official', 'committee_id' => $sportCommittee->id]
    );

    $license = License::factory()->create([
        'committee_id' => $sportCommittee->id,
        'professional_role_id' => $techRole->id,
    ]);
    $certification = Certification::factory()->create([
        'committee_id' => $sportCommittee->id,
        'professional_role_id' => $techRole->id,
        'license_id' => $license->id,
    ]);

    $visibleOfficial = Individual::factory()->create([
        'name' => 'VisibleOfficial',
        'surname' => 'Person',
        'visible_in_technical_official_registry' => true,
    ]);

    $hiddenOfficial = Individual::factory()->create([
        'name' => 'HiddenOfficial',
        'surname' => 'Person',
        'visible_in_technical_official_registry' => false,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $visibleOfficial->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $visibleOfficial->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $hiddenOfficial->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $hiddenOfficial->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('VisibleOfficial')
        ->assertDontSee('HiddenOfficial');
});

test('diving professionals registry only shows individuals with visible_in_diving_professional_registry enabled', function () {
    $divingServicesCommittee = Committee::firstOrCreate(
        ['code' => 'DIVINGSERVICES'],
        ['name' => 'Diving Services', 'is_international' => false]
    );

    $license = License::factory()->create([
        'committee_id' => $divingServicesCommittee->id,
    ]);

    $visibleDiver = Individual::factory()->create([
        'name' => 'VisibleDiver',
        'surname' => 'Person',
        'visible_in_diving_professional_registry' => true,
    ]);

    $hiddenDiver = Individual::factory()->create([
        'name' => 'HiddenDiver',
        'surname' => 'Person',
        'visible_in_diving_professional_registry' => false,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $visibleDiver->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $hiddenDiver->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingProfessionals::class)
        ->assertSee('VisibleDiver')
        ->assertDontSee('HiddenDiver');
});

test('privacy form component renders with current individual values', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => true,
        'visible_in_technical_official_registry' => false,
        'visible_in_diving_professional_registry' => true,
    ]);

    $this->actingAs($individual->user);

    Livewire::test(UpdateRegistryPrivacyForm::class)
        ->assertSet('visible_in_coach_registry', true)
        ->assertSet('visible_in_technical_official_registry', false)
        ->assertSet('visible_in_diving_professional_registry', true);
});

test('privacy form component updates registry visibility', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => false,
        'visible_in_technical_official_registry' => false,
        'visible_in_diving_professional_registry' => false,
    ]);

    $this->actingAs($individual->user);

    Livewire::test(UpdateRegistryPrivacyForm::class)
        ->assertSet('visible_in_coach_registry', false)
        ->assertSet('visible_in_technical_official_registry', false)
        ->assertSet('visible_in_diving_professional_registry', false)
        ->set('visible_in_coach_registry', true)
        ->set('visible_in_technical_official_registry', true)
        ->set('visible_in_diving_professional_registry', true)
        ->call('updateRegistryPrivacy')
        ->assertDispatched('saved');

    $individual->refresh();

    expect($individual->visible_in_coach_registry)->toBeTrue()
        ->and($individual->visible_in_technical_official_registry)->toBeTrue()
        ->and($individual->visible_in_diving_professional_registry)->toBeTrue();
});

test('privacy form auto-saves when checkbox is toggled', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => true,
    ]);

    $this->actingAs($individual->user);

    Livewire::test(UpdateRegistryPrivacyForm::class)
        ->set('visible_in_coach_registry', false)
        ->assertDispatched('saved');

    $individual->refresh();
    expect($individual->visible_in_coach_registry)->toBeFalse();
});

test('privacy form component appears on jetstream profile page for users with individual', function () {
    $individual = Individual::factory()->create();

    $this->actingAs($individual->user)
        ->get('/user/profile')
        ->assertSeeLivewire(UpdateRegistryPrivacyForm::class);
});

test('toggling visibility persists to database', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => false,
        'visible_in_technical_official_registry' => false,
        'visible_in_diving_professional_registry' => false,
    ]);

    $individual->update([
        'visible_in_coach_registry' => true,
        'visible_in_technical_official_registry' => true,
        'visible_in_diving_professional_registry' => true,
    ]);

    $individual->refresh();

    expect($individual->visible_in_coach_registry)->toBeTrue()
        ->and($individual->visible_in_technical_official_registry)->toBeTrue()
        ->and($individual->visible_in_diving_professional_registry)->toBeTrue();

    $individual->update([
        'visible_in_coach_registry' => false,
    ]);

    $individual->refresh();

    expect($individual->visible_in_coach_registry)->toBeFalse()
        ->and($individual->visible_in_technical_official_registry)->toBeTrue()
        ->and($individual->visible_in_diving_professional_registry)->toBeTrue();
});
