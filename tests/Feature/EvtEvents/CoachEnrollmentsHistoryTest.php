<?php

use App\Livewire\Admin\EvtEvents\CoachEnrollmentsHistoryTable;
use App\Models\Group;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\Federations\Models\Federation;
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

it('renders the coach enrollments history page', function () {
    $this->get(route('admin.evt-events.coach-enrollments-history.index'))
        ->assertSuccessful()
        ->assertSeeLivewire(CoachEnrollmentsHistoryTable::class);
});

it('displays only active coach enrollments', function () {
    $activeEnrollment = CoachEnrollment::factory()->create([
        'status_class' => RegisteredCoachEnrollmentState::class,
    ]);

    $canceledEnrollment = CoachEnrollment::factory()->create([
        'status_class' => CanceledCoachEnrollmentState::class,
    ]);

    Livewire\Livewire::test(CoachEnrollmentsHistoryTable::class)
        ->assertCanSeeTableRecords([$activeEnrollment])
        ->assertCanNotSeeTableRecords([$canceledEnrollment]);
});

it('can filter by sport', function () {
    $event = Event::factory()->create();
    $competition = Competition::factory()->create(['event_id' => $event->id]);

    $enrollment = CoachEnrollment::factory()->create([
        'event_id' => $event->id,
        'status_class' => RegisteredCoachEnrollmentState::class,
    ]);

    Livewire\Livewire::test(CoachEnrollmentsHistoryTable::class)
        ->filterTable('sport', $competition->sport_id)
        ->assertCanSeeTableRecords([$enrollment]);
});

it('can search by member number', function () {
    $enrollment = CoachEnrollment::factory()->create([
        'status_class' => RegisteredCoachEnrollmentState::class,
    ]);

    $memberNumber = $enrollment->individual->member_number;

    Livewire\Livewire::test(CoachEnrollmentsHistoryTable::class)
        ->searchTable($memberNumber)
        ->assertCanSeeTableRecords([$enrollment]);
});
