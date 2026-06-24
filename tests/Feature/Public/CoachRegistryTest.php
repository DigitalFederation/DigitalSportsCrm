<?php

use App\Livewire\Public\CoachRegistry;
use App\Models\Committee;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Livewire\Livewire;

beforeEach(function () {
    $this->sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $this->coachRole = ProfessionalRole::firstOrCreate(
        ['role' => 'COACH', 'code' => 'TESTCOACH'],
        ['name' => 'Test Coach', 'committee_id' => $this->sportCommittee->id]
    );

    $this->certification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->coachRole->id,
        'name' => 'Sport Coach Certification',
    ]);
});

test('coach registry page can be rendered', function () {
    $response = $this->get(route('public.coach-registry'));

    $response->assertStatus(200);
    $response->assertSeeLivewire(CoachRegistry::class);
});

test('it displays coaches with active SPORT+COACH certifications', function () {
    $individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'Coach',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('John')
        ->assertSee('Coach');
});

test('it displays coaches with expired SPORT+COACH certifications', function () {
    $individual = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'ExpiredCoach',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('Jane')
        ->assertSee('ExpiredCoach');
});

test('it displays coaches with suspended SPORT+COACH certifications', function () {
    $individual = Individual::factory()->create([
        'name' => 'Mike',
        'surname' => 'SuspendedCoach',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => SuspendedCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('Mike')
        ->assertSee('SuspendedCoach');
});

test('it filters coaches by name', function () {
    $john = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'TestCoach',
        'visible_in_coach_registry' => true,
    ]);

    $jane = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'OtherCoach',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $john->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $jane->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->set('searchName', 'John')
        ->assertSee('John')
        ->assertSee('TestCoach')
        ->assertDontSee('OtherCoach');
});

test('it filters coaches by district', function () {
    $district = District::factory()->create(['is_active' => true]);
    $otherDistrict = District::factory()->create(['is_active' => true]);

    $johnInDistrict = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'LocalCoach',
        'district_id' => $district->id,
        'visible_in_coach_registry' => true,
    ]);

    $janeInOtherDistrict = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'FarCoach',
        'district_id' => $otherDistrict->id,
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $johnInDistrict->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $janeInOtherDistrict->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->set('selectedDistrict', $district->id)
        ->assertSee('LocalCoach')
        ->assertDontSee('FarCoach');
});

test('it filters coaches by certification status', function () {
    $activeIndividual = Individual::factory()->create([
        'name' => 'ActiveCoachPerson',
        'surname' => 'Smith',
        'visible_in_coach_registry' => true,
    ]);

    $expiredIndividual = Individual::factory()->create([
        'name' => 'ExpiredCoachPerson',
        'surname' => 'Jones',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $activeIndividual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $expiredIndividual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->set('selectedStatus', 'active')
        ->assertSee('ActiveCoachPerson')
        ->assertDontSee('ExpiredCoachPerson');

    Livewire::test(CoachRegistry::class)
        ->set('selectedStatus', 'expired')
        ->assertSee('ExpiredCoachPerson')
        ->assertDontSee('ActiveCoachPerson');
});

test('it can clear all filters', function () {
    $district = District::factory()->create(['is_active' => true]);

    Livewire::test(CoachRegistry::class)
        ->set('searchName', 'John')
        ->set('selectedDistrict', $district->id)
        ->set('selectedStatus', 'active')
        ->call('clearFilters')
        ->assertSet('searchName', '')
        ->assertSet('selectedDistrict', '')
        ->assertSet('selectedStatus', '');
});

test('it does not display individuals without COACH role certifications', function () {
    $nonCoachRole = ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $nonCoachCertification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $nonCoachRole->id,
    ]);

    $individual = Individual::factory()->create([
        'name' => 'NotA',
        'surname' => 'CoachPerson',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $nonCoachCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertDontSee('NotA')
        ->assertDontSee('CoachPerson');
});

test('it shows only the most recent certification when coach has multiple', function () {
    $newerCertification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->coachRole->id,
        'name' => 'Newer Coach Cert',
    ]);

    $individual = Individual::factory()->create([
        'name' => 'Multi',
        'surname' => 'CertCoach',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
        'current_term_starts_at' => '2024-01-01',
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $newerCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_starts_at' => '2025-06-01',
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('Multi')
        ->assertSee('CertCoach')
        ->assertSee('Newer Coach Cert')
        ->assertDontSee('Sport Coach Certification');
});

test('it does not display individuals with COACH certifications from other committees', function () {
    $otherCommittee = Committee::factory()->create(['code' => 'DIVINGSERVICES']);
    $coachRole = ProfessionalRole::factory()->create(['role' => 'COACH']);
    $otherCertification = Certification::factory()->create([
        'committee_id' => $otherCommittee->id,
        'professional_role_id' => $coachRole->id,
    ]);

    $individual = Individual::factory()->create([
        'name' => 'DivingOnly',
        'surname' => 'CoachNotSport',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $otherCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertDontSee('DivingOnly')
        ->assertDontSee('CoachNotSport');
});

test('it displays both certification status and license status column headers', function () {
    $individual = Individual::factory()->create([
        'name' => 'ColumnTest',
        'surname' => 'Headers',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee(__('public.coach_registry.table.certification_status'))
        ->assertSee(__('public.coach_registry.table.license_status'));
});

test('it displays license status from license data, not certification data', function () {
    $individual = Individual::factory()->create([
        'name' => 'LicenseTest',
        'surname' => 'CoachPerson',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $license = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->coachRole->id,
        'name' => 'Sport Coach License',
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('LicenseTest')
        ->assertSee('CoachPerson');
});

test('it shows dash when coach has no license', function () {
    $individual = Individual::factory()->create([
        'name' => 'NoLicense',
        'surname' => 'CoachDash',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(CoachRegistry::class)
        ->assertSee('NoLicense')
        ->assertSee('CoachDash');
});
