<?php

use App\Livewire\Public\TechnicalOfficialRegistry;
use App\Models\Committee;
use App\Models\Sport;
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
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Livewire\Livewire;

beforeEach(function () {
    $this->sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $this->technicalOfficialRole = ProfessionalRole::firstOrCreate(
        ['role' => 'TECHNICAL_OFFICIAL', 'code' => 'TESTTECHOFF'],
        ['name' => 'Test Technical Official', 'committee_id' => $this->sportCommittee->id]
    );

    $this->certification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'name' => 'Technical Official Certification',
    ]);
});

test('technical official registry page can be rendered', function () {
    $response = $this->get(route('public.technical-official-registry'));

    $response->assertStatus(200);
    $response->assertSeeLivewire(TechnicalOfficialRegistry::class);
});

test('it displays individuals with active SPORT+TECHNICAL_OFFICIAL certifications', function () {
    $individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'Official',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('John')
        ->assertSee('Official');
});

test('it displays individuals with expired SPORT+TECHNICAL_OFFICIAL certifications', function () {
    $individual = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'ExpiredOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('Jane')
        ->assertSee('ExpiredOfficial');
});

test('it displays individuals with suspended SPORT+TECHNICAL_OFFICIAL certifications', function () {
    $individual = Individual::factory()->create([
        'name' => 'Mike',
        'surname' => 'SuspendedOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => SuspendedCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('Mike')
        ->assertSee('SuspendedOfficial');
});

test('it filters officials by name', function () {
    $john = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'TestOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    $jane = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'OtherOfficial',
        'visible_in_technical_official_registry' => true,
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

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('searchName', 'John')
        ->assertSee('John')
        ->assertSee('TestOfficial')
        ->assertDontSee('OtherOfficial');
});

test('it filters officials by district', function () {
    $district = District::factory()->create(['is_active' => true]);
    $otherDistrict = District::factory()->create(['is_active' => true]);

    $johnInDistrict = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'LocalOfficial',
        'district_id' => $district->id,
        'visible_in_technical_official_registry' => true,
    ]);

    $janeInOtherDistrict = Individual::factory()->create([
        'name' => 'Jane',
        'surname' => 'FarOfficial',
        'district_id' => $otherDistrict->id,
        'visible_in_technical_official_registry' => true,
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

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('selectedDistrict', $district->id)
        ->assertSee('LocalOfficial')
        ->assertDontSee('FarOfficial');
});

test('it filters officials by certification status', function () {
    $activeIndividual = Individual::factory()->create([
        'name' => 'ActiveOfficialPerson',
        'surname' => 'Smith',
        'visible_in_technical_official_registry' => true,
    ]);

    $expiredIndividual = Individual::factory()->create([
        'name' => 'ExpiredOfficialPerson',
        'surname' => 'Jones',
        'visible_in_technical_official_registry' => true,
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

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('selectedStatus', 'active')
        ->assertSee('ActiveOfficialPerson')
        ->assertDontSee('ExpiredOfficialPerson');

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('selectedStatus', 'expired')
        ->assertSee('ExpiredOfficialPerson')
        ->assertDontSee('ActiveOfficialPerson');
});

test('it can clear all filters', function () {
    $district = District::factory()->create(['is_active' => true]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('searchName', 'John')
        ->set('selectedDistrict', $district->id)
        ->set('selectedStatus', 'active')
        ->call('clearFilters')
        ->assertSet('searchName', '')
        ->assertSet('selectedDistrict', '')
        ->assertSet('selectedStatus', '');
});

test('it does not display individuals without TECHNICAL_OFFICIAL role certifications', function () {
    $coachRole = ProfessionalRole::factory()->create(['role' => 'COACH']);
    $coachCertification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $coachRole->id,
    ]);

    $individual = Individual::factory()->create([
        'name' => 'NotA',
        'surname' => 'TechOfficialPerson',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $coachCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertDontSee('NotA')
        ->assertDontSee('TechOfficialPerson');
});

test('it does not display individuals with TECHNICAL_OFFICIAL certifications from other committees', function () {
    $otherCommittee = Committee::factory()->create(['code' => 'DIVINGSERVICES']);
    $techRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $otherCertification = Certification::factory()->create([
        'committee_id' => $otherCommittee->id,
        'professional_role_id' => $techRole->id,
    ]);

    $individual = Individual::factory()->create([
        'name' => 'DivingOnly',
        'surname' => 'OfficialNotSport',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $otherCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertDontSee('DivingOnly')
        ->assertDontSee('OfficialNotSport');
});

test('it displays all certification badges when official has multiple certifications', function () {
    $secondCertification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'name' => 'Second Technical Certification',
    ]);

    $individual = Individual::factory()->create([
        'name' => 'Multi',
        'surname' => 'CertOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $secondCertification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('Multi')
        ->assertSee('CertOfficial')
        ->assertSee('Technical Official Certification')
        ->assertSee('Second Technical Certification');
});

test('it displays certification status and license status column headers', function () {
    $individual = Individual::factory()->create([
        'name' => 'ColumnTest',
        'surname' => 'Headers',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee(__('public.technical_official_registry.table.certification_status'))
        ->assertSee(__('public.technical_official_registry.table.license_status'));
});

test('it displays license status when official has matching license', function () {
    $license = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'name' => 'Technical Official License',
    ]);

    $certificationWithLicense = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'license_id' => $license->id,
        'name' => 'Cert With License',
    ]);

    $individual = Individual::factory()->create([
        'name' => 'LicensedOfficial',
        'surname' => 'WithLicense',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $certificationWithLicense->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('LicensedOfficial')
        ->assertSee('WithLicense')
        ->assertSee('Cert With License');
});

test('it shows dash when official has no license', function () {
    $individual = Individual::factory()->create([
        'name' => 'NoLicenseOfficial',
        'surname' => 'DashTest',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('NoLicenseOfficial')
        ->assertSee('DashTest');
});

test('it filters officials by sport', function () {
    $sportA = Sport::factory()->create(['name' => 'Finswimming Test']);
    $sportB = Sport::factory()->create(['name' => 'Freediving Test']);

    $licenseA = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'sport_id' => $sportA->id,
    ]);

    $licenseB = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'sport_id' => $sportB->id,
    ]);

    $certA = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'license_id' => $licenseA->id,
    ]);

    $certB = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'license_id' => $licenseB->id,
    ]);

    $individualA = Individual::factory()->create([
        'name' => 'SportA',
        'surname' => 'OfficialFilter',
        'visible_in_technical_official_registry' => true,
    ]);

    $individualB = Individual::factory()->create([
        'name' => 'SportB',
        'surname' => 'OfficialFilter',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individualA->id,
        'certification_id' => $certA->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individualB->id,
        'certification_id' => $certB->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('selectedSport', (string) $sportA->id)
        ->assertSee('SportA')
        ->assertDontSee('SportB');
});

test('clear filters resets sport selection', function () {
    $sport = Sport::factory()->create();

    Livewire::test(TechnicalOfficialRegistry::class)
        ->set('selectedSport', (string) $sport->id)
        ->call('clearFilters')
        ->assertSet('selectedSport', '');
});

test('it sorts by active certification first, then active license, then alphabetical', function () {
    $license = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
    ]);

    $certWithLicense = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->technicalOfficialRole->id,
        'license_id' => $license->id,
    ]);

    // Individual A: active certification + active license (should appear 1st)
    $individualA = Individual::factory()->create([
        'name' => 'Anna',
        'surname' => 'ActiveBoth',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individualA->id,
        'certification_id' => $certWithLicense->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => 'individual',
        'model_id' => $individualA->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Individual B: active certification + no active license (should appear 2nd)
    $individualB = Individual::factory()->create([
        'name' => 'Bruno',
        'surname' => 'ActiveCertOnly',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individualB->id,
        'certification_id' => $certWithLicense->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    // Individual C: expired certification + no active license (should appear 3rd)
    $individualC = Individual::factory()->create([
        'name' => 'Carlos',
        'surname' => 'NoActive',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individualC->id,
        'certification_id' => $this->certification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
    ]);

    // Individual D: expired certification + no active license, alphabetically after C by name
    $individualD = Individual::factory()->create([
        'name' => 'Zara',
        'surname' => 'Zebra',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individualD->id,
        'certification_id' => $this->certification->id,
        'status_class' => ExpiredCertificationAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSeeInOrder(['ActiveBoth', 'ActiveCertOnly', 'NoActive', 'Zebra']);
});
