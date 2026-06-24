<?php

use App\Livewire\EvtEvents\OfficialHistory;
use App\Models\User;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\EvtEvents\Models\Sport as EvtSport;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createEventWithSportForOfficialHistory(array $eventAttrs = []): array
{
    $event = Event::factory()->create($eventAttrs);
    $competition = Competition::factory()->create([
        'event_id' => $event->id,
    ]);

    // Sync sport to evt_sports table so the Domain Sport model can resolve it
    $evtSport = EvtSport::unguarded(fn () => EvtSport::firstOrCreate(
        ['id' => $competition->sport_id],
        ['name' => \App\Models\Sport::find($competition->sport_id)->name]
    ));

    return [$event, $competition, $evtSport];
}

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $individualGroup = \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);
    $this->user = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create([
        'user_id' => $this->user->id,
    ]);

    [$this->event, $this->competition, $this->sport] = createEventWithSportForOfficialHistory([
        'name' => 'Test Championship 2026',
        'start_date' => '2026-01-15',
        'end_date' => '2026-01-17',
    ]);

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('renders the official history component', function () {
    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertSuccessful();
});

it('shows active referee enrollments in the table', function () {
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertSee('Test Championship 2026');
});

it('shows evaluated and activated enrollments in history', function () {
    // Simulate: enrollment starts pending, gets evaluated, becomes active
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
        'evaluation_notes' => 'Evaluated and activated',
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertSee('Test Championship 2026')
        ->assertSee('4 - ' . __('events.evaluation_excellent'));
});

it('does not show pending enrollments in history', function () {
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => \Domain\EvtEvents\States\PendingRefereeEnrollmentState::class,
        'evaluation' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertDontSee('Test Championship 2026');
});

it('does not show canceled enrollments', function () {
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => CanceledRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertDontSee('Test Championship 2026');
});

it('shows evaluation with label', function () {
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
        'evaluation_notes' => 'Very good performance',
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertSee('4 - ' . __('events.evaluation_excellent'));
});

it('shows function assignments', function () {
    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => true,
        'function_text' => 'Chief Timekeeper',
        'assigned_by' => $this->individual->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertSee('Chief Timekeeper');
});

it('computes sport summaries correctly', function () {
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
    ]);

    [$event2] = createEventWithSportForOfficialHistory([
        'name' => 'Second Championship',
        'start_date' => '2025-06-10',
        'end_date' => '2025-06-12',
    ]);
    // Update to same sport so they group together
    $event2->competition->update(['sport_id' => $this->sport->id]);

    $enrollment2 = Enrollment::factory()->create([
        'event_id' => $event2->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment2->id,
        'event_id' => $event2->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 2,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(OfficialHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(1)
        ->and($summaries->first()->total_events)->toBe(2)
        ->and($summaries->first()->average_evaluation)->toBe(3.0)
        ->and($summaries->first()->total_experience_points)->toBe(6)
        ->and($summaries->first()->since_year)->toBe('2025');
});

it('returns empty summaries when no enrollments exist', function () {
    $component = Livewire::actingAs($this->user)
        ->test(OfficialHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(0);
});

it('shows chief_judge events in the chief_judge tab', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->call('setActiveTab', 'chief_judge')
        ->assertSee('Test Championship 2026');
});

it('does not show chief_judge events in the referees tab', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertDontSee('Test Championship 2026');
});

it('computes sport summaries merging both data sources', function () {
    // Referee enrollment with evaluation 4
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
    ]);

    // Chief judge event in same sport
    [$event2] = createEventWithSportForOfficialHistory([
        'name' => 'Second Championship',
        'start_date' => '2025-06-10',
        'end_date' => '2025-06-12',
    ]);
    $event2->competition->update(['sport_id' => $this->sport->id]);

    EventRole::create([
        'event_id' => $event2->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(OfficialHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(1)
        ->and($summaries->first()->total_events)->toBe(2)
        // Experience: referee eval 4 + chief_judge 10 = 14
        ->and($summaries->first()->total_experience_points)->toBe(14)
        // Average: (4 + 5.0) / 2 = 4.5
        ->and($summaries->first()->average_evaluation)->toBe(4.5)
        ->and($summaries->first()->since_year)->toBe('2025');
});

it('tab switching works between referees and chief_judge', function () {
    $component = Livewire::actingAs($this->user)
        ->test(OfficialHistory::class)
        ->assertSet('activeTab', 'referees')
        ->call('setActiveTab', 'chief_judge')
        ->assertSet('activeTab', 'chief_judge')
        ->call('setActiveTab', 'referees')
        ->assertSet('activeTab', 'referees');
});

it('counts referees and chief_judge separately', function () {
    RefereeEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(OfficialHistory::class);

    expect($component->instance()->refereesCount)->toBe(1)
        ->and($component->instance()->chiefJudgeCount)->toBe(1)
        ->and($component->instance()->historyCount)->toBe(2);
});
