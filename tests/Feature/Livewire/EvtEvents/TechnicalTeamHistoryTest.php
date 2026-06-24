<?php

use App\Livewire\EvtEvents\TechnicalTeamHistory;
use App\Models\User;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\Sport as EvtSport;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeamEventWithSport(array $eventAttrs = []): array
{
    $event = Event::factory()->create($eventAttrs);
    $competition = Competition::factory()->create([
        'event_id' => $event->id,
    ]);

    $evtSport = EvtSport::unguarded(fn () => EvtSport::firstOrCreate(
        ['id' => $competition->sport_id],
        ['name' => \App\Models\Sport::find($competition->sport_id)->name]
    ));

    return [$event, $competition, $evtSport];
}

beforeEach(function () {
    $individualGroup = \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);
    $this->user = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create([
        'user_id' => $this->user->id,
    ]);

    [$this->event, $this->competition, $this->sport] = createTeamEventWithSport([
        'name' => 'Test Championship 2026',
        'start_date' => '2026-01-15',
        'end_date' => '2026-01-17',
    ]);
});

it('renders the technical team history component', function () {
    Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class)
        ->assertSuccessful();
});

it('shows events where user is technical_delegate', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class)
        ->assertSee('Test Championship 2026');
});

it('does not show chief_judge roles', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class)
        ->assertDontSee('Test Championship 2026');
});

it('does not show events where user has no role', function () {
    Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class)
        ->assertDontSee('Test Championship 2026');
});

it('does not show competition_director roles', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_COMPETITION_DIRECTOR,
    ]);

    Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class)
        ->assertDontSee('Test Championship 2026');
});

it('displays correct role badge for technical_delegate', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class)
        ->assertSee(__('events.technical_delegate'));
});

it('computes sport summaries with technical_delegate getting zero experience', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(1)
        ->and($summaries->first()->total_events)->toBe(1)
        ->and($summaries->first()->total_experience_points)->toBeNull()
        ->and($summaries->first()->average_evaluation)->toBeNull();
});

it('does not include chief_judge in sport summaries', function () {
    // Only a chief_judge role - should not appear in TechnicalTeamHistory summaries
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(0);
});

it('computes multiple delegate events in same sport correctly', function () {
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    [$event2] = createTeamEventWithSport([
        'name' => 'Second Championship',
        'start_date' => '2025-06-10',
        'end_date' => '2025-06-12',
    ]);
    $event2->competition->update(['sport_id' => $this->sport->id]);

    EventRole::create([
        'event_id' => $event2->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(1)
        ->and($summaries->first()->total_events)->toBe(2)
        ->and($summaries->first()->total_experience_points)->toBeNull()
        ->and($summaries->first()->average_evaluation)->toBeNull()
        ->and($summaries->first()->since_year)->toBe('2025');
});

it('returns empty summaries when no roles exist', function () {
    $component = Livewire::actingAs($this->user)
        ->test(TechnicalTeamHistory::class);

    $summaries = $component->instance()->getSportSummaries();

    expect($summaries)->toHaveCount(0);
});
