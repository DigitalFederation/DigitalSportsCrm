<?php

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Exceptions\EnrollmentValidationException;
use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Actions\PreRegisterParticipantsAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {

    // Create and authenticate user
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $this->user = User::factory()->create(['group_id' => $group->id]);
    $this->actingAs($this->user);

    $this->event = Event::factory()->create([
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'allow_coach_enrollment' => true,
        'allow_referee_enrollment' => true,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addDay(),
    ]);
    $this->federation = Federation::factory()->create();
    $this->user->federations()->attach($this->federation->id);
    // Create pricing for different roles
    $this->pricings = collect([
        'athlete' => Pricing::factory()->create([
            'event_id' => $this->event->id,
            'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE,
            'price' => 100,
            'is_active' => true,
        ]),
        'coach' => Pricing::factory()->create([
            'event_id' => $this->event->id,
            'enrollment_role' => EvtEventEnrollmentRoleEnum::COACH,
            'price' => 50,
            'is_active' => true,
        ]),
    ]);
});

it('validates event enrollment permissions', function () {
    $this->event->update([
        'allow_coach_enrollment' => false,
    ]);

    $coach = Individual::factory()->create();
    $participants = [
        'coach' => [['id' => $coach->id]],
    ];

    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, $participants))
        ->toThrow(EnrollmentValidationException::class, 'Coach enrollment is not allowed for this event.');
});

it('handles empty participant lists', function () {
    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, []))
        ->toThrow(EnrollmentValidationException::class, 'No athletes provided for enrollment.');
});

it('validates participant data format', function () {
    $participants = [
        'athlete' => [['invalid_format' => true]],
    ];

    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, $participants))
        ->toThrow(EnrollmentValidationException::class);
});
