<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtIndividualEnrollmentStatusEnum;
use App\Models\Group;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\IndividualEnrollmentAttribute;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\AssignedTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
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

    $this->event = Event::factory()->create();

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $this->actingAs($this->user);
});

// ── Athlete Cancel ──────────────────────────────────────────────────────────

it('admin can cancel an athlete enrollment', function () {
    $athleteEnrollment = AthleteEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.athlete.destroy', [
        'event' => $this->event,
        'athleteEnrollment' => $athleteEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($athleteEnrollment->fresh()->status_class)
        ->toBe(EvtAthleteEnrollmentStatusEnum::CANCELED);
});

it('admin can permanently delete a canceled athlete enrollment', function () {
    $athleteEnrollment = AthleteEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtAthleteEnrollmentStatusEnum::CANCELED,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.athlete.force-delete', [
        'event' => $this->event,
        'athleteEnrollment' => $athleteEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(AthleteEnrollment::withTrashed()->find($athleteEnrollment->id))->toBeNull();
});

it('admin cannot permanently delete a non-canceled athlete enrollment', function () {
    $athleteEnrollment = AthleteEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.athlete.force-delete', [
        'event' => $this->event,
        'athleteEnrollment' => $athleteEnrollment,
    ]))
        ->assertForbidden();
});

// ── Coach Cancel ────────────────────────────────────────────────────────────

it('admin can cancel a coach enrollment', function () {
    $coachEnrollment = CoachEnrollment::factory()
        ->forEvent($this->event)
        ->create([
            'enrollment_id' => $this->enrollment->id,
            'status_class' => RegisteredCoachEnrollmentState::class,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.coach.destroy', [
        'event' => $this->event,
        'coach_enrollment' => $coachEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($coachEnrollment->fresh()->status_class)
        ->toBe(CanceledCoachEnrollmentState::class);
});

it('admin can permanently delete a canceled coach enrollment', function () {
    $coachEnrollment = CoachEnrollment::factory()
        ->forEvent($this->event)
        ->canceled()
        ->create([
            'enrollment_id' => $this->enrollment->id,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.coach.force-delete', [
        'event' => $this->event,
        'coach_enrollment' => $coachEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(CoachEnrollment::find($coachEnrollment->id))->toBeNull();
});

it('admin cannot permanently delete a non-canceled coach enrollment', function () {
    $coachEnrollment = CoachEnrollment::factory()
        ->forEvent($this->event)
        ->create([
            'enrollment_id' => $this->enrollment->id,
            'status_class' => AssignedCoachEnrollmentState::class,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.coach.force-delete', [
        'event' => $this->event,
        'coach_enrollment' => $coachEnrollment,
    ]))
        ->assertForbidden();
});

// ── Team Official Cancel ────────────────────────────────────────────────────

it('admin can cancel a team official enrollment', function () {
    $officialEnrollment = TeamOfficialEnrollment::factory()
        ->forEvent($this->event)
        ->create([
            'enrollment_id' => $this->enrollment->id,
            'status_class' => RegisteredTeamOfficialEnrollmentState::class,
        ]);

    $this->delete(route('admin.evt-events.events.officials-enrollment.destroy', [
        'event' => $this->event,
        'officials_enrollment' => $officialEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($officialEnrollment->fresh()->status_class)
        ->toBe(CanceledTeamOfficialEnrollmentState::class);
});

it('admin can permanently delete a canceled team official enrollment', function () {
    $officialEnrollment = TeamOfficialEnrollment::factory()
        ->forEvent($this->event)
        ->withStatus(CanceledTeamOfficialEnrollmentState::class)
        ->create([
            'enrollment_id' => $this->enrollment->id,
        ]);

    $this->delete(route('admin.evt-events.events.officials-enrollment.force-delete', [
        'event' => $this->event,
        'officials_enrollment' => $officialEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(TeamOfficialEnrollment::find($officialEnrollment->id))->toBeNull();
});

it('admin cannot permanently delete a non-canceled team official enrollment', function () {
    $officialEnrollment = TeamOfficialEnrollment::factory()
        ->forEvent($this->event)
        ->create([
            'enrollment_id' => $this->enrollment->id,
            'status_class' => AssignedTeamOfficialEnrollmentState::class,
        ]);

    $this->delete(route('admin.evt-events.events.officials-enrollment.force-delete', [
        'event' => $this->event,
        'officials_enrollment' => $officialEnrollment,
    ]))
        ->assertForbidden();
});

// ── Registered Views ────────────────────────────────────────────────────────

it('canceled athlete enrollments appear in registered view', function () {
    $canceledEnrollment = AthleteEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtAthleteEnrollmentStatusEnum::CANCELED,
        ]);

    $this->get(route('admin.evt-events.events.enrollments.athlete.registered', $this->event))
        ->assertSuccessful()
        ->assertSee($canceledEnrollment->individual->name);
});

it('canceled coach enrollments appear in registered view', function () {
    $canceledEnrollment = CoachEnrollment::factory()
        ->forEvent($this->event)
        ->canceled()
        ->create([
            'enrollment_id' => $this->enrollment->id,
        ]);

    $this->get(route('admin.evt-events.events.enrollments.coach.registered', $this->event))
        ->assertSuccessful()
        ->assertSee($canceledEnrollment->individual->name);
});

it('canceled team official enrollments appear in registered view', function () {
    $canceledEnrollment = TeamOfficialEnrollment::factory()
        ->forEvent($this->event)
        ->withStatus(CanceledTeamOfficialEnrollmentState::class)
        ->create([
            'enrollment_id' => $this->enrollment->id,
        ]);

    $this->get(route('admin.evt-events.events.officials-enrollment.registered', $this->event))
        ->assertSuccessful()
        ->assertSee($canceledEnrollment->individual->name);
});

// ── Individual Enrollment Delete ───────────────────────────────────────────

it('admin can delete an individual enrollment', function () {
    $individualEnrollment = IndividualEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtIndividualEnrollmentStatusEnum::REGISTERED->value,
        ]);

    $this->delete(route('admin.evt-events.events.enrollments.individual.destroy', [
        'event' => $this->event,
        'individualEnrollment' => $individualEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(IndividualEnrollment::find($individualEnrollment->id))->toBeNull();
});

it('non-admin cannot delete an individual enrollment', function () {
    $nonAdminGroup = Group::factory()->create(['code' => 'FEDERATION']);
    $nonAdminUser = UserFactory::new()->create([
        'group_id' => $nonAdminGroup->id,
    ]);
    $nonAdminUser->assignRole('federation-admin');

    $individualEnrollment = IndividualEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtIndividualEnrollmentStatusEnum::REGISTERED->value,
        ]);

    $this->actingAs($nonAdminUser)
        ->delete(route('admin.evt-events.events.enrollments.individual.destroy', [
            'event' => $this->event,
            'individualEnrollment' => $individualEnrollment,
        ]))
        ->assertForbidden();

    expect(IndividualEnrollment::find($individualEnrollment->id))->not->toBeNull();
});

it('deleting individual enrollment also deletes related attributes', function () {
    $individualEnrollment = IndividualEnrollment::factory()
        ->forEnrollment($this->enrollment)
        ->create([
            'status_class' => EvtIndividualEnrollmentStatusEnum::REGISTERED->value,
        ]);

    $attribute = Attribute::factory()->create();

    IndividualEnrollmentAttribute::create([
        'individual_enrollment_id' => $individualEnrollment->id,
        'attribute_id' => $attribute->id,
        'value' => 'test-value',
    ]);

    expect($individualEnrollment->attributes()->count())->toBe(1);

    $this->delete(route('admin.evt-events.events.enrollments.individual.destroy', [
        'event' => $this->event,
        'individualEnrollment' => $individualEnrollment,
    ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(IndividualEnrollment::find($individualEnrollment->id))->toBeNull();
    expect(IndividualEnrollmentAttribute::where('individual_enrollment_id', $individualEnrollment->id)->count())->toBe(0);
});
