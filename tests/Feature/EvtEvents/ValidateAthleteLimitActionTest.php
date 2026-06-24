<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\ValidateAthleteLimitAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->discipline = Discipline::factory()->create([
        'athlete_limit' => 2, // Set the athlete limit to 2 for this test
    ]);

    $this->federation = Federation::factory()->create();
    $this->entity = Entity::factory()->create();
    $this->event = Event::factory()->create();

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('validates the athlete limit for a federation', function () {
    $firstAthlete = Individual::factory()->create();
    $secondAthlete = Individual::factory()->create();
    $thirdAthlete = Individual::factory()->create();

    // Enroll first two athletes with default (likely active) status
    AthleteEnrollment::factory()->forFederation($this->federation)->create([
        'discipline_id' => $this->discipline->id,
        'individual_id' => $firstAthlete->id,
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    AthleteEnrollment::factory()->forFederation($this->federation)->create([
        'discipline_id' => $this->discipline->id,
        'individual_id' => $secondAthlete->id,
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;

    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->federation, [$thirdAthlete]);
    expect($result['valid'])->toBeFalse();
    expect($result['message'])->toBe('The number of selected individuals exceeds the limit of 2 for this discipline.');
});

it('validates the athlete limit for an entity', function () {
    $firstAthlete = Individual::factory()->create();
    $secondAthlete = Individual::factory()->create();
    $thirdAthlete = Individual::factory()->create();

    $this->enrollment->update([
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Enroll first two athletes
    AthleteEnrollment::factory()->forEntity($this->entity)->create([
        'discipline_id' => $this->discipline->id,
        'individual_id' => $firstAthlete->id,
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    AthleteEnrollment::factory()->forEntity($this->entity)->create([
        'discipline_id' => $this->discipline->id,
        'individual_id' => $secondAthlete->id,
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;

    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->entity, [$thirdAthlete]);
    expect($result['valid'])->toBeFalse();
    expect($result['message'])->toBe('The number of selected individuals exceeds the limit of 2 for this discipline.');
});

it('allows enrolling athletes within the limit for a federation', function () {
    $firstAthlete = Individual::factory()->create();
    $secondAthlete = Individual::factory()->create();

    // No enrollments yet

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;

    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->federation, [$firstAthlete, $secondAthlete]);
    expect($result['valid'])->toBeTrue();
    expect($result['message'])->toBeNull();
});

it('allows enrolling athletes within the limit for an entity', function () {
    $firstAthlete = Individual::factory()->create();
    $secondAthlete = Individual::factory()->create();

    // No enrollments yet

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;

    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->entity, [$firstAthlete, $secondAthlete]);
    expect($result['valid'])->toBeTrue();
    expect($result['message'])->toBeNull();
});

it('prevents enrollment if limit is met by PENDING_PAYMENT status', function () {
    $this->discipline->update(['athlete_limit' => 1]); // Set limit to 1

    $existingAthlete = Individual::factory()->create();
    $newAthlete = Individual::factory()->create();

    AthleteEnrollment::factory()->forFederation($this->federation)->create([
        'discipline_id' => $this->discipline->id,
        'individual_id' => $existingAthlete->id,
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value, // Pending
    ]);

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;
    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->federation, [$newAthlete]);

    // Expect false because PENDING_PAYMENT (if counted) + new athlete exceeds limit of 1
    // This test assumes the ValidateAthleteLimitAction counts PENDING_PAYMENT.
    expect($result['valid'])->toBeFalse();
    expect($result['message'])->toBe('The number of selected individuals exceeds the limit of 1 for this discipline.');
});

it('prevents enrollment if limit already met by PAID athletes', function () {
    $this->discipline->update(['athlete_limit' => 1]); // Limit is 1

    $paidAthlete = Individual::factory()->create();
    $newAthlete = Individual::factory()->create();

    AthleteEnrollment::factory()->forFederation($this->federation)->create([
        'discipline_id' => $this->discipline->id,
        'individual_id' => $paidAthlete->id,
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value, // Active
    ]);

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;
    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->federation, [$newAthlete]);

    // Expect false: 1 PAID + 1 newAthlete > limit of 1
    expect($result['valid'])->toBeFalse();
    expect($result['message'])->toBe('The number of selected individuals exceeds the limit of 1 for this discipline.');
});

it('correctly validates when no existing enrollments and adding multiple within limit', function () {
    $this->discipline->update(['athlete_limit' => 3]); // Limit is 3
    $athletesToEnroll = Individual::factory(3)->create()->all();

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;
    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->federation, $athletesToEnroll);

    expect($result['valid'])->toBeTrue();
    expect($result['message'])->toBeNull();
});

it('correctly validates when no existing enrollments and adding multiple exceeding limit', function () {
    $this->discipline->update(['athlete_limit' => 2]); // Limit is 2
    $athletesToEnroll = Individual::factory(3)->create()->all(); // Trying to enroll 3

    $validateAthleteLimitAction = new ValidateAthleteLimitAction;
    $result = $validateAthleteLimitAction->execute($this->discipline, $this->event->id, $this->federation, $athletesToEnroll);

    expect($result['valid'])->toBeFalse();
    expect($result['message'])->toBe('The number of selected individuals exceeds the limit of 2 for this discipline.');
});
