<?php

use App\Livewire\Public\TechnicalOfficialProfile;
use App\Livewire\Public\TechnicalOfficialRegistry;
use App\Models\Committee;
use App\Models\Sport;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Livewire\Livewire;

beforeEach(function () {
    $this->sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $this->officialRole = ProfessionalRole::firstOrCreate(
        ['role' => 'TECHNICAL_OFFICIAL', 'code' => 'TESTOFFICIAL'],
        ['name' => 'Test Official', 'committee_id' => $this->sportCommittee->id]
    );

    $this->sport = Sport::factory()->create(['name' => 'Underwater Hockey']);

    $this->license = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->officialRole->id,
        'name' => 'Sport Official License',
        'sport_id' => $this->sport->id,
    ]);

    $this->certification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'license_id' => $this->license->id,
        'professional_role_id' => $this->officialRole->id,
        'name' => 'Referee Level 1',
    ]);
});

test('technical official profile page renders for visible officials', function () {
    $individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'ProfileOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    $response = $this->get(route('public.technical-official-profile', $individual));

    $response->assertSuccessful();
    $response->assertSeeLivewire(TechnicalOfficialProfile::class);
});

test('technical official profile page returns 404 for hidden officials', function () {
    $individual = Individual::factory()->create([
        'visible_in_technical_official_registry' => false,
    ]);

    $response = $this->get(route('public.technical-official-profile', $individual));

    $response->assertNotFound();
});

test('technical official profile page shows correct data', function () {
    $district = District::factory()->create(['is_active' => true, 'name' => 'Lisboa']);

    $individual = Individual::factory()->create([
        'name' => 'Maria',
        'surname' => 'TestOfficial',
        'district_id' => $district->id,
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee('Maria')
        ->assertSee('TestOfficial')
        ->assertSee('Lisboa');
});

test('technical official profile page shows certification status badges', function () {
    $individual = Individual::factory()->create([
        'name' => 'Paulo',
        'surname' => 'BadgeOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ExpiredCertificationAttributedState::class,
        'activated_at' => now()->subYear(),
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee('Paulo')
        ->assertSee('BadgeOfficial')
        ->assertSee('Referee Level 1')
        ->assertSee(__('public.technical_official_registry.status.expired'));
});

test('technical official profile shows certifications table with sport name', function () {
    $individual = Individual::factory()->create([
        'name' => 'Ana',
        'surname' => 'CertOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee('Referee Level 1')
        ->assertSee(__('public.technical_official_registry.status.active'));
});

test('technical official profile deduplicates certifications by sport showing most recent', function () {
    $individual = Individual::factory()->create([
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1 Old',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now()->subYear(),
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 2 New',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee('Referee Level 2 New')
        ->assertDontSee('Referee Level 1 Old');
});

test('technical official profile shows referee enrollments in events table', function () {
    $individual = Individual::factory()->create([
        'name' => 'Diana',
        'surname' => 'EventOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    $event = Event::factory()->create([
        'name' => 'National Championship',
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-03',
    ]);
    $entity = Entity::factory()->create(['name' => 'Test Club']);

    RefereeEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $event->id,
        'entity_id' => $entity->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee('National Championship')
        ->assertSee('01/06/2025')
        ->assertSee('03/06/2025')
        ->assertSee('Test Club');
});

test('technical official profile shows empty state when no events', function () {
    $individual = Individual::factory()->create([
        'name' => 'Elena',
        'surname' => 'EmptyOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee(__('public.technical_official_registry.profile.no_competitions'));
});

test('technical official profile only shows active referee enrollments', function () {
    $individual = Individual::factory()->create([
        'name' => 'Felix',
        'surname' => 'FilterOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    $activeEvent = Event::factory()->create(['name' => 'Visible Event']);
    RefereeEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $activeEvent->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    $canceledEvent = Event::factory()->create(['name' => 'Canceled Event']);
    RefereeEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $canceledEvent->id,
        'status_class' => CanceledRefereeEnrollmentState::class,
    ]);

    $pendingEvent = Event::factory()->create(['name' => 'Pending Event']);
    RefereeEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $pendingEvent->id,
        'status_class' => PendingRefereeEnrollmentState::class,
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee('Visible Event')
        ->assertDontSee('Canceled Event')
        ->assertDontSee('Pending Event');
});

test('technical official registry table renders with view profile links', function () {
    $individual = Individual::factory()->create([
        'name' => 'Ana',
        'surname' => 'TableOfficial',
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(TechnicalOfficialRegistry::class)
        ->assertSee('Ana')
        ->assertSee('TableOfficial')
        ->assertSeeHtml(route('public.technical-official-profile', $individual));
});

test('technical official profile shows license status when official has matching license', function () {
    $individual = Individual::factory()->create([
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSee(__('public.technical_official_registry.status.active'));
});

test('technical official profile shows dash when official has no license', function () {
    $individual = Individual::factory()->create([
        'visible_in_technical_official_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Referee Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(TechnicalOfficialProfile::class, ['individual' => $individual])
        ->assertSeeHtml('<span class="text-sm text-gray-400">-</span>');
});
