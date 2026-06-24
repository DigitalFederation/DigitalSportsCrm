<?php

declare(strict_types=1);

use App\Livewire\Public\EventsCalendar;
use App\Livewire\Public\EventShow;
use App\Models\Sport;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('renders the public events calendar without authentication', function () {
    $this->get(route('public.events'))
        ->assertOk()
        ->assertSeeLivewire(EventsCalendar::class);
});

it('shows visible upcoming events and hides invisible ones', function () {
    $visible = Event::factory()->create([
        'name' => 'Visible Future Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(11),
    ]);

    $hidden = Event::factory()->create([
        'name' => 'Hidden Event',
        'is_visible' => false,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(11),
    ]);

    Livewire::test(EventsCalendar::class)
        ->assertSee($visible->name)
        ->assertDontSee($hidden->name);
});

it('excludes archived and canceled events', function () {
    Event::factory()->create([
        'name' => 'Archived Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ArchiveEventState::class,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(6),
    ]);

    Event::factory()->create([
        'name' => 'Canceled Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => CanceledEventState::class,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(6),
    ]);

    Livewire::test(EventsCalendar::class)
        ->assertDontSee('Archived Event')
        ->assertDontSee('Canceled Event');
});

it('filters by type=competition', function () {
    $competition = Event::factory()->create([
        'name' => 'Competition Alpha',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);

    $organization = Event::factory()->create([
        'name' => 'Organization Beta',
        'is_visible' => true,
        'event_category' => 'organization',
        'organization_type' => 'coach_course',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);

    Livewire::test(EventsCalendar::class)
        ->set('type', 'competition')
        ->assertSee($competition->name)
        ->assertDontSee($organization->name);
});

it('filters competitions by sport_id', function () {
    $sportA = Sport::factory()->create(['name' => 'Test Sport A']);
    $sportB = Sport::factory()->create(['name' => 'Test Sport B']);

    $eventA = Event::factory()->create([
        'name' => 'Event With Sport A',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);
    Competition::factory()->create([
        'event_id' => $eventA->id,
        'sport_id' => $sportA->id,
    ]);

    $eventB = Event::factory()->create([
        'name' => 'Event With Sport B',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);
    Competition::factory()->create([
        'event_id' => $eventB->id,
        'sport_id' => $sportB->id,
    ]);

    Livewire::test(EventsCalendar::class)
        ->set('sportId', (string) $sportA->id)
        ->assertSee($eventA->name)
        ->assertDontSee($eventB->name);
});

it('hides past events by default and shows them when includePast is enabled', function () {
    $past = Event::factory()->create([
        'name' => 'Past Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(8),
    ]);

    Livewire::test(EventsCalendar::class)
        ->assertDontSee($past->name)
        ->set('includePast', true)
        ->assertSee($past->name);
});

it('respects the date range filter', function () {
    $insideRange = Event::factory()->create([
        'name' => 'Inside Range Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(20),
        'end_date' => now()->addDays(21),
    ]);

    $outsideRange = Event::factory()->create([
        'name' => 'Outside Range Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(60),
        'end_date' => now()->addDays(61),
    ]);

    Livewire::test(EventsCalendar::class)
        ->set('dateFrom', now()->addDays(15)->toDateString())
        ->set('dateTo', now()->addDays(30)->toDateString())
        ->assertSee($insideRange->name)
        ->assertDontSee($outsideRange->name);
});

it('ignores invalid public date filters instead of failing', function () {
    $event = Event::factory()->create([
        'name' => 'Visible Event With Invalid Query Filters',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(20),
        'end_date' => now()->addDays(21),
    ]);

    Livewire::test(EventsCalendar::class)
        ->set('dateFrom', 'not-a-date')
        ->set('dateTo', '2026-99-99')
        ->assertSee($event->name);
});

it('event detail returns 404 for invisible events', function () {
    $event = Event::factory()->create([
        'is_visible' => false,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);

    $this->get(route('public.event.show', $event))->assertNotFound();
});

it('event detail renders for visible events', function () {
    $event = Event::factory()->create([
        'name' => 'Public Detail Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);

    $this->get(route('public.event.show', $event))
        ->assertOk()
        ->assertSee($event->name)
        ->assertSeeLivewire(EventShow::class);
});

it('escapes event notes on the public detail page', function () {
    $event = Event::factory()->create([
        'name' => 'Safe Public Detail Event',
        'notes' => '<p><strong>Allowed note</strong></p><img src=x onerror="alert(1)"><script>alert("owned")</script>',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(3),
    ]);

    $this->get(route('public.event.show', $event))
        ->assertOk()
        ->assertSee('<strong>Allowed note</strong>', false)
        ->assertDontSee('<script>alert("owned")</script>', false)
        ->assertDontSee('onerror="alert(1)"', false);
});

it('switches between list, grid and calendar views', function () {
    Livewire::test(EventsCalendar::class)
        ->assertSet('view', 'list')
        ->call('setView', 'grid')
        ->assertSet('view', 'grid')
        ->call('setView', 'calendar')
        ->assertSet('view', 'calendar')
        ->call('setView', 'list')
        ->assertSet('view', 'list')
        ->call('setView', 'invalid')
        ->assertSet('view', 'list');
});

it('toggles calendar month/year mode', function () {
    Livewire::test(EventsCalendar::class)
        ->call('setView', 'calendar')
        ->assertSet('calendarMode', 'month')
        ->call('setCalendarMode', 'year')
        ->assertSet('calendarMode', 'year')
        ->call('nextPeriod')
        ->call('previousPeriod')
        ->call('setCalendarMode', 'month')
        ->assertSet('calendarMode', 'month');
});

it('applies date and past filters to calendar events', function () {
    Carbon::setTestNow('2026-05-12 12:00:00');

    try {
        $early = Event::factory()->create([
            'name' => 'Early June Event',
            'is_visible' => true,
            'event_category' => 'competition',
            'status_class' => ActiveEventState::class,
            'start_date' => Carbon::parse('2026-06-04'),
            'end_date' => Carbon::parse('2026-06-05'),
        ]);

        $late = Event::factory()->create([
            'name' => 'Late June Event',
            'is_visible' => true,
            'event_category' => 'competition',
            'status_class' => ActiveEventState::class,
            'start_date' => Carbon::parse('2026-06-20'),
            'end_date' => Carbon::parse('2026-06-21'),
        ]);

        Livewire::test(EventsCalendar::class)
            ->set('view', 'calendar')
            ->set('calendarYear', 2026)
            ->set('calendarMonth', 6)
            ->set('dateFrom', '2026-06-15')
            ->set('dateTo', '2026-06-30')
            ->tap(function ($component) use ($early, $late) {
                $names = $component->instance()->calendarEvents->pluck('name');

                expect($names)
                    ->not->toContain($early->name)
                    ->toContain($late->name);
            });
    } finally {
        Carbon::setTestNow();
    }
});

it('focusMonth jumps to a specific month and switches to month view', function () {
    Livewire::test(EventsCalendar::class)
        ->set('calendarMode', 'year')
        ->call('focusMonth', 7)
        ->assertSet('calendarMonth', 7)
        ->assertSet('calendarMode', 'month');
});

it('stats include events count, distinct organizers and total event days', function () {
    $event = Event::factory()->create([
        'name' => 'Three Day Event',
        'is_visible' => true,
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(5)->startOfDay(),
        'end_date' => now()->addDays(7)->startOfDay(),
    ]);

    \Domain\EvtEvents\Models\Organizer::factory()->create([
        'event_id' => $event->id,
    ]);

    Livewire::test(EventsCalendar::class)
        ->assertSet('view', 'list')
        ->tap(function ($component) {
            $stats = $component->instance()->stats;
            expect($stats['events'])->toBe(1)
                ->and($stats['organizers'])->toBe(1)
                ->and($stats['event_days'])->toBe(3);
        });
});

it('clearFilters resets filter state', function () {
    Livewire::test(EventsCalendar::class)
        ->set('sportId', '1')
        ->set('type', 'competition')
        ->set('includePast', true)
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-12-31')
        ->call('clearFilters')
        ->assertSet('sportId', '')
        ->assertSet('type', '')
        ->assertSet('includePast', false)
        ->assertSet('dateFrom', null)
        ->assertSet('dateTo', null);
});
