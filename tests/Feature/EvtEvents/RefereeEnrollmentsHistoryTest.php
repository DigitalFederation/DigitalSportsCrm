<?php

use App\Livewire\Admin\EvtEvents\RefereeEnrollmentsHistoryTable;
use App\Models\Group;
use App\Models\Sport;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create(['code' => 'ADMIN']);
    $this->user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    $this->user->assignRole('admin');

    $this->federation = Federation::factory()->create(['is_default_federation' => true]);

    $this->actingAs($this->user);
});

/**
 * Helper to create an event with a competition linked to a sport.
 * Creates the sport in `sports` table (for FK) and a matching record in `evt_sports` (for Eloquent relation)
 * with the same ID so the BelongsTo resolves correctly.
 */
function createEventWithSport(?string $sportName = null): object
{
    $sport = Sport::factory()->create($sportName ? ['name' => $sportName] : []);

    // Create matching evt_sports record with same ID for the Eloquent relation
    \Illuminate\Support\Facades\DB::table('evt_sports')->insert([
        'id' => $sport->id,
        'name' => $sport->name,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $evtSport = \Domain\EvtEvents\Models\Sport::find($sport->id);

    $event = Event::factory()->create();
    $competition = Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $sport->id,
    ]);

    return (object) [
        'sport' => $sport,
        'evtSport' => $evtSport,
        'event' => $event,
        'competition' => $competition,
    ];
}

it('renders the referee enrollments history page', function () {
    $this->get(route('admin.evt-events.referee-enrollments-history.index'))
        ->assertSuccessful()
        ->assertSeeLivewire(RefereeEnrollmentsHistoryTable::class);
});

it('displays only active referee enrollments', function () {
    $activeEnrollment = RefereeEnrollment::factory()->create([
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    $canceledEnrollment = RefereeEnrollment::factory()->create([
        'status_class' => CanceledRefereeEnrollmentState::class,
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->assertCanSeeTableRecords([$activeEnrollment])
        ->assertCanNotSeeTableRecords([$canceledEnrollment]);
});

it('can filter by sport', function () {
    $event = Event::factory()->create();
    $competition = Competition::factory()->create(['event_id' => $event->id]);

    $enrollment = RefereeEnrollment::factory()->create([
        'event_id' => $event->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->filterTable('sport', $competition->sport_id)
        ->assertCanSeeTableRecords([$enrollment]);
});

it('can search by member number', function () {
    $enrollment = RefereeEnrollment::factory()->create([
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    $memberNumber = $enrollment->individual->member_number;

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->searchTable($memberNumber)
        ->assertCanSeeTableRecords([$enrollment]);
});

it('can switch to evaluation tab', function () {
    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->assertSet('activeTab', 'history')
        ->call('setActiveTab', 'evaluation')
        ->assertSet('activeTab', 'evaluation')
        ->assertSee(__('events.admin_referee_evaluation_tab'));
});

it('can switch back to history tab', function () {
    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->call('setActiveTab', 'evaluation')
        ->assertSet('activeTab', 'evaluation')
        ->call('setActiveTab', 'history')
        ->assertSet('activeTab', 'history');
});

it('displays evaluation ranking with correct data', function () {
    $setup = createEventWithSport();
    $individual = Individual::factory()->create();

    RefereeEnrollment::factory()->create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
    ]);

    RefereeEnrollment::factory()->create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 3,
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->call('setActiveTab', 'evaluation')
        ->assertSee($individual->full_name);
});

it('calculates experience points correctly from evaluations', function () {
    $setup = createEventWithSport();
    $individual = Individual::factory()->create();

    RefereeEnrollment::factory()->create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
    ]);

    RefereeEnrollment::factory()->create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 3,
    ]);

    $component = new RefereeEnrollmentsHistoryTable;
    $component->activeTab = 'evaluation';
    $ranking = $component->getEvaluationRanking();

    $row = $ranking->firstWhere('individual_name', $individual->full_name);

    expect($row)->not->toBeNull()
        ->and($row->experience_points)->toBe(7)
        ->and($row->average_level)->toBe(3.5);
});

it('includes chief judge experience points in evaluation ranking', function () {
    $setup = createEventWithSport();
    $individual = Individual::factory()->create();

    EventRole::create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    $component = new RefereeEnrollmentsHistoryTable;
    $component->activeTab = 'evaluation';
    $ranking = $component->getEvaluationRanking();

    $row = $ranking->firstWhere('individual_name', $individual->full_name);

    expect($row)->not->toBeNull()
        ->and($row->experience_points)->toBe(10)
        ->and($row->average_level)->toBe(5.0);
});

it('filters evaluation ranking by sport', function () {
    $setup1 = createEventWithSport('Finswimming');
    $setup2 = createEventWithSport('Freediving');

    $individual1 = Individual::factory()->create(['name' => 'FilterSportA', 'surname' => 'TestA']);
    $individual2 = Individual::factory()->create(['name' => 'FilterSportB', 'surname' => 'TestB']);

    RefereeEnrollment::factory()->create([
        'event_id' => $setup1->event->id,
        'individual_id' => $individual1->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
    ]);

    RefereeEnrollment::factory()->create([
        'event_id' => $setup2->event->id,
        'individual_id' => $individual2->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 3,
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->call('setActiveTab', 'evaluation')
        ->set('evalSportFilter', $setup1->evtSport->id)
        ->assertSee('FilterSportA TestA')
        ->assertDontSee('FilterSportB TestB');
});

it('filters evaluation ranking by name', function () {
    $setup = createEventWithSport();

    $individual1 = Individual::factory()->create(['name' => 'Example', 'surname' => 'Official']);
    $individual2 = Individual::factory()->create(['name' => 'Sample', 'surname' => 'Official']);

    RefereeEnrollment::factory()->create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual1->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
    ]);

    RefereeEnrollment::factory()->create([
        'event_id' => $setup->event->id,
        'individual_id' => $individual2->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 3,
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->call('setActiveTab', 'evaluation')
        ->set('evalNameFilter', 'Example')
        ->assertSee('Example Official')
        ->assertDontSee('Sample Official');
});

it('sorts evaluation ranking by column', function () {
    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->call('setActiveTab', 'evaluation')
        ->assertSet('evalSortBy', 'experience_points')
        ->assertSet('evalSortDir', 'desc')
        ->call('sortEvaluation', 'individual_name')
        ->assertSet('evalSortBy', 'individual_name')
        ->assertSet('evalSortDir', 'desc')
        ->call('sortEvaluation', 'individual_name')
        ->assertSet('evalSortDir', 'asc');
});

it('shows viewEvaluationNotes action when evaluation_notes is set', function () {
    $enrollment = RefereeEnrollment::factory()->create([
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 4,
        'evaluation_notes' => 'Great performance during the event.',
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->assertTableActionVisible('viewEvaluationNotes', $enrollment);
});

it('hides viewEvaluationNotes action when evaluation_notes is null', function () {
    $enrollment = RefereeEnrollment::factory()->create([
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 3,
        'evaluation_notes' => null,
    ]);

    Livewire\Livewire::test(RefereeEnrollmentsHistoryTable::class)
        ->assertTableActionHidden('viewEvaluationNotes', $enrollment);
});
