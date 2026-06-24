<?php

use App\Livewire\EvtEvents\CoachHistory;
use App\Models\User;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Sport as EvtSport;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createCoachEventWithSport(array $eventAttrs = []): array
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

    [$this->event, $this->competition, $this->sport] = createCoachEventWithSport([
        'name' => 'Coach Championship 2026',
        'start_date' => '2026-01-15',
        'end_date' => '2026-01-17',
    ]);
});

it('renders the coach history component', function () {
    Livewire::actingAs($this->user)
        ->test(CoachHistory::class)
        ->assertSuccessful();
});

it('shows registered coach enrollments', function () {
    CoachEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($this->individual)
        ->create([
            'status_class' => RegisteredCoachEnrollmentState::class,
        ]);

    Livewire::actingAs($this->user)
        ->test(CoachHistory::class)
        ->assertSee('Coach Championship 2026');
});

it('does not show canceled coach enrollments', function () {
    CoachEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($this->individual)
        ->canceled()
        ->create();

    Livewire::actingAs($this->user)
        ->test(CoachHistory::class)
        ->assertDontSee('Coach Championship 2026');
});

it('does not show events where user has no coach enrollment', function () {
    Livewire::actingAs($this->user)
        ->test(CoachHistory::class)
        ->assertDontSee('Coach Championship 2026');
});

it('can filter by sport', function () {
    CoachEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($this->individual)
        ->create([
            'status_class' => RegisteredCoachEnrollmentState::class,
        ]);

    Livewire::actingAs($this->user)
        ->test(CoachHistory::class)
        ->filterTable('sport', $this->sport->id)
        ->assertSee('Coach Championship 2026');
});

it('can search by event name', function () {
    CoachEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($this->individual)
        ->create([
            'status_class' => RegisteredCoachEnrollmentState::class,
        ]);

    Livewire::actingAs($this->user)
        ->test(CoachHistory::class)
        ->searchTable('Coach Championship')
        ->assertSee('Coach Championship 2026');
});

it('computes history count correctly', function () {
    CoachEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($this->individual)
        ->create([
            'status_class' => RegisteredCoachEnrollmentState::class,
        ]);

    $component = Livewire::actingAs($this->user)
        ->test(CoachHistory::class);

    expect($component->instance()->historyCount)->toBe(1);
});

it('returns zero history count when no enrollments exist', function () {
    $component = Livewire::actingAs($this->user)
        ->test(CoachHistory::class);

    expect($component->instance()->historyCount)->toBe(0);
});
