<?php

use App\Livewire\Public\CoachProfile;
use App\Models\Committee;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
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

    $this->coachRole = ProfessionalRole::firstOrCreate(
        ['role' => 'COACH', 'code' => 'TESTCOACH'],
        ['name' => 'Test Coach', 'committee_id' => $this->sportCommittee->id]
    );

    $this->license = License::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'professional_role_id' => $this->coachRole->id,
        'name' => 'Sport Coach License',
    ]);

    $this->certification = Certification::factory()->create([
        'committee_id' => $this->sportCommittee->id,
        'license_id' => $this->license->id,
        'professional_role_id' => $this->coachRole->id,
        'name' => 'Coach Certification Level 1',
    ]);
});

test('coach profile page renders for visible coaches', function () {
    $individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'ProfileCoach',
        'visible_in_coach_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $response = $this->get(route('public.coach-profile', $individual));

    $response->assertSuccessful();
    $response->assertSeeLivewire(CoachProfile::class);
});

test('coach profile page returns 404 for hidden coaches', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => false,
    ]);

    $response = $this->get(route('public.coach-profile', $individual));

    $response->assertNotFound();
});

test('coach profile page shows correct data', function () {
    $district = District::factory()->create(['is_active' => true, 'name' => 'Lisboa']);

    $individual = Individual::factory()->create([
        'name' => 'Maria',
        'surname' => 'TestCoach',
        'district_id' => $district->id,
        'visible_in_coach_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee('Maria')
        ->assertSee('TestCoach')
        ->assertSee('Lisboa');
});

test('coach profile page shows license status badges', function () {
    $individual = Individual::factory()->create([
        'name' => 'Paulo',
        'surname' => 'BadgeCoach',
        'visible_in_coach_registry' => true,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ExpiredLicenseAttributedState::class,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee('Paulo')
        ->assertSee('BadgeCoach');
});

test('registry table renders with view profile links', function () {
    $individual = Individual::factory()->create([
        'name' => 'Ana',
        'surname' => 'TableCoach',
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Coach Certification Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(\App\Livewire\Public\CoachRegistry::class)
        ->assertSee('Ana')
        ->assertSee('TableCoach')
        ->assertSeeHtml(route('public.coach-profile', $individual));
});

test('coach profile shows gender when present', function () {
    $individual = Individual::factory()->create([
        'name' => 'Carlos',
        'surname' => 'InfoCoach',
        'birthdate' => '1990-05-15',
        'gender' => 'male',
        'visible_in_coach_registry' => true,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee('Carlos')
        ->assertSee('InfoCoach')
        ->assertDontSee('1990')
        ->assertSee(__('individuals.male'));
});

test('coach profile shows competitions table with enrollment data', function () {
    $individual = Individual::factory()->create([
        'name' => 'Diana',
        'surname' => 'CompCoach',
        'visible_in_coach_registry' => true,
    ]);

    $event = Event::factory()->create([
        'name' => 'National Championship',
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-03',
    ]);
    $entity = Entity::factory()->create(['name' => 'Test Club']);

    CoachEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $event->id,
        'entity_id' => $entity->id,
        'status_class' => RegisteredCoachEnrollmentState::class,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee('National Championship')
        ->assertSee('01/06/2025')
        ->assertSee('03/06/2025')
        ->assertSee('Test Club');
});

test('coach profile shows empty state when no competitions', function () {
    $individual = Individual::factory()->create([
        'name' => 'Elena',
        'surname' => 'EmptyCoach',
        'visible_in_coach_registry' => true,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee(__('public.coach_registry.profile.no_competitions'));
});

test('coach profile only shows registered and assigned enrollments', function () {
    $individual = Individual::factory()->create([
        'name' => 'Felix',
        'surname' => 'FilterCoach',
        'visible_in_coach_registry' => true,
    ]);

    $registeredEvent = Event::factory()->create(['name' => 'Visible Event']);
    CoachEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $registeredEvent->id,
        'status_class' => RegisteredCoachEnrollmentState::class,
    ]);

    $assignedEvent = Event::factory()->create(['name' => 'Assigned Event']);
    CoachEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $assignedEvent->id,
        'status_class' => AssignedCoachEnrollmentState::class,
    ]);

    $canceledEvent = Event::factory()->create(['name' => 'Canceled Event']);
    CoachEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $canceledEvent->id,
        'status_class' => CanceledCoachEnrollmentState::class,
    ]);

    $pendingEvent = Event::factory()->create(['name' => 'Pending Event']);
    CoachEnrollment::factory()->create([
        'individual_id' => $individual->id,
        'event_id' => $pendingEvent->id,
        'status_class' => PendingCoachEnrollmentState::class,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee('Visible Event')
        ->assertSee('Assigned Event')
        ->assertDontSee('Canceled Event')
        ->assertDontSee('Pending Event');
});

test('coach profile shows license status when coach has matching license via certification', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Coach Certification Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $this->license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSee(__('public.coach_registry.status.active'));
});

test('coach profile shows dash when coach has no matching license', function () {
    $individual = Individual::factory()->create([
        'visible_in_coach_registry' => true,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $individual->id,
        'certification_id' => $this->certification->id,
        'certification_name' => 'Coach Certification Level 1',
        'status_class' => ActiveCertificationAttributedState::class,
        'activated_at' => now(),
    ]);

    Livewire::test(CoachProfile::class, ['individual' => $individual])
        ->assertSeeHtml('<span class="text-sm text-gray-400">-</span>');
});
