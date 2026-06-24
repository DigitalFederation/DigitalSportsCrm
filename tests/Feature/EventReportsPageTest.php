<?php

use App\Models\Group;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\EvtEvents\Models\TechnicalDelegateReport;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create(['code' => 'ADMIN']);
    $this->admin = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    $this->admin->assignRole('admin');

    $this->event = Event::factory()->create([
        'name' => 'Test Championship',
        'location' => 'Lisbon',
    ]);
});

it('loads the reports page successfully for admin', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSuccessful()
        ->assertSee('Test Championship');
});

it('displays the header card with event name and location', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee('Test Championship')
        ->assertSee('Lisbon')
        ->assertSee(__('events.admin_reports_title'));
});

it('shows summary cards for TD, CJ, and referees', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee(__('events.technical_delegate'))
        ->assertSee(__('events.chief_judge'))
        ->assertSee(__('events.technical_officials'));
});

it('displays TD and CJ names when assigned', function () {
    $tdIndividual = Individual::factory()->create(['name' => 'John', 'surname' => 'Delegate']);
    $cjIndividual = Individual::factory()->create(['name' => 'Jane', 'surname' => 'Judge']);

    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $tdIndividual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $cjIndividual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee('John')
        ->assertSee('Delegate')
        ->assertSee('Jane')
        ->assertSee('Judge');
});

it('shows not assigned label when no TD or CJ', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee(__('events.not_assigned'));
});

it('displays submitted TD report sections', function () {
    $individual = Individual::factory()->create();

    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $individual->id,
        'participants_withdrawals' => 'All athletes present',
        'incidents_occurrences' => 'No incidents',
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee('All athletes present')
        ->assertSee('No incidents')
        ->assertSee(__('events.report_status_submitted'));
});

it('shows competition director when assigned', function () {
    $director = Individual::factory()->create(['name' => 'Carlos', 'surname' => 'Director']);

    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $director->id,
        'role' => EventRole::ROLE_COMPETITION_DIRECTOR,
    ]);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee('Carlos')
        ->assertSee('Director')
        ->assertSee(__('events.competition_director_label'));
});

it('displays referee assignments with all functions as stacked badges', function () {
    $individual = Individual::factory()->create(['name' => 'Manuel', 'surname' => 'Referee']);
    $assignedBy = Individual::factory()->create();

    $enrollment = RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create();

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $enrollment->id,
        'function_text' => 'Head Judge',
        'is_present' => true,
        'competition_days' => 3,
        'number_of_games' => 5,
        'assigned_by' => $assignedBy->id,
    ]);

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $enrollment->id,
        'function_text' => 'Safety Officer',
        'is_present' => true,
        'competition_days' => 2,
        'number_of_games' => 3,
        'assigned_by' => $assignedBy->id,
    ]);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee('Manuel')
        ->assertSee('Referee')
        ->assertSee('Head Judge')
        ->assertSee('Safety Officer');
});

it('displays evaluation badges with correct labels', function () {
    $individual = Individual::factory()->create(['name' => 'Eva', 'surname' => 'Luated']);

    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create(['evaluation' => 4, 'evaluation_notes' => 'Great performance']);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee(__('events.evaluation_excellent'))
        ->assertSee('Great performance');
});

it('shows empty state when no referees are enrolled', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee(__('events.no_referees_enrolled_in_event'));
});

it('shows tab navigation labels', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee(__('events.reports_tab'))
        ->assertSee(__('events.referee_assignments_tab'));
});

it('shows notes icon button instead of truncated text when evaluation notes exist', function () {
    $individual = Individual::factory()->create(['name' => 'Notes', 'surname' => 'Tester']);

    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create(['evaluation' => 3, 'evaluation_notes' => 'Very detailed evaluation notes for this referee']);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSuccessful()
        ->assertSee('Very detailed evaluation notes for this referee');
});

it('shows notes icon for assignment notes', function () {
    $individual = Individual::factory()->create();
    $assignedBy = Individual::factory()->create();

    $enrollment = RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create();

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $enrollment->id,
        'function_text' => 'Judge',
        'is_present' => true,
        'notes' => 'Assignment specific note here',
        'assigned_by' => $assignedBy->id,
    ]);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSuccessful()
        ->assertSee('Assignment specific note here');
});

it('renders the notes modal markup', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSuccessful()
        ->assertSee('notesModalOpen')
        ->assertSee('notesModalContent');
});

it('shows export buttons when referees exist', function () {
    $individual = Individual::factory()->create();

    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create();

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSuccessful()
        ->assertSee(__('events.export_report_excel'))
        ->assertSee(__('events.export_report_pdf'));
});

it('exports excel report successfully', function () {
    $individual = Individual::factory()->create();

    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create();

    actingAs($this->admin);

    $this->post(route('admin.evt-events.events.reports.export-excel', $this->event))
        ->assertSuccessful();
});

it('exports pdf report successfully', function () {
    $individual = Individual::factory()->create();

    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forIndividual($individual)
        ->create();

    actingAs($this->admin);

    $this->post(route('admin.evt-events.events.reports.export-pdf', $this->event))
        ->assertSuccessful();
});

it('computes presence stats correctly', function () {
    $ind1 = Individual::factory()->create();
    $ind2 = Individual::factory()->create();
    $assignedBy = Individual::factory()->create();

    $enr1 = RefereeEnrollment::factory()->forEvent($this->event)->forIndividual($ind1)->create();
    $enr2 = RefereeEnrollment::factory()->forEvent($this->event)->forIndividual($ind2)->create();

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $enr1->id,
        'function_text' => 'Judge',
        'is_present' => true,
        'assigned_by' => $assignedBy->id,
    ]);

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $enr2->id,
        'function_text' => 'Timer',
        'is_present' => false,
        'assigned_by' => $assignedBy->id,
    ]);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.events.reports', $this->event))
        ->assertSee(__('events.referees_present', ['count' => 1, 'total' => 2]));
});
