<?php

use App\Enums\EvtAttributeTypesEnum;
use Domain\EvtEvents\Actions\ValidateOutOfRaceEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\AthleteEnrollmentAttributes;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->federation = Federation::factory()->create();
    $this->discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'athlete_limit' => 2,
    ]);

    // Create out-of-race attribute
    $this->outOfRaceAttribute = Attribute::factory()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE->value,
    ]);

    // Attach attribute to discipline
    $this->discipline->attributes()->attach($this->outOfRaceAttribute);
});

test('allows enrollment for relay/team disciplines regardless of out-of-race attribute', function () {
    $relayDiscipline = Discipline::factory()->create([
        'enrollment_type' => 'relay',
        'athlete_limit' => 2,
    ]);

    $errorMessages = [];
    $result = (new ValidateOutOfRaceEnrollmentAction)->execute(
        $relayDiscipline,
        $this->event->id,
        $this->federation,
        [],
        $errorMessages
    );

    expect($result)->toBeTrue()
        ->and($errorMessages)->toBeEmpty();
});

test('allows enrollment when discipline has no out-of-race attribute', function () {
    $regularDiscipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'athlete_limit' => 2,
    ]);

    $errorMessages = [];
    $result = (new ValidateOutOfRaceEnrollmentAction)->execute(
        $regularDiscipline,
        $this->event->id,
        $this->federation,
        [],
        $errorMessages
    );

    expect($result)->toBeTrue()
        ->and($errorMessages)->toBeEmpty();
});

test('validates in-race athletes count correctly', function () {
    // Create existing in-race enrollment
    $athleteEnrollment = AthleteEnrollment::factory()->create([
        'discipline_id' => $this->discipline->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
    ]);

    // Create the attribute separately
    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    $errorMessages = [];
    $result = (new ValidateOutOfRaceEnrollmentAction)->execute(
        $this->discipline,
        $this->event->id,
        $this->federation,
        [
            1 => [$this->outOfRaceAttribute->id => 'no'],
            2 => [$this->outOfRaceAttribute->id => 'no'],
        ],
        $errorMessages
    );

    expect($result)->toBeFalse()
        ->and($errorMessages)->toContain("The number of in-race athletes (3) exceeds the limit of 2 for {$this->discipline->name}.");
});

test('allows unlimited out-of-race athletes', function () {
    // Create multiple existing in-race enrollments
    $athleteEnrollment = AthleteEnrollment::factory()->count(2)->create([
        'discipline_id' => $this->discipline->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
    ]);

    foreach ($athleteEnrollment as $enroll) {
        // Create the attribute separately
        AthleteEnrollmentAttributes::create([
            'athlete_enrollment_id' => $enroll->id,
            'attribute_id' => $this->outOfRaceAttribute->id,
            'value' => 'no',
        ]);
    }

    $errorMessages = [];
    $result = (new ValidateOutOfRaceEnrollmentAction)->execute(
        $this->discipline,
        $this->event->id,
        $this->federation,
        [
            1 => [$this->outOfRaceAttribute->id => 'yes'], // out-of-race
            2 => [$this->outOfRaceAttribute->id => 'yes'], // out-of-race
            3 => [$this->outOfRaceAttribute->id => 'yes'],  // out-of-race
        ],
        $errorMessages
    );

    expect($result)->toBeTrue()
        ->and($errorMessages)->toBeEmpty();
});

test('shouldSkipInitialValidation returns true only for individual disciplines with out-of-race attribute', function () {
    $validator = new ValidateOutOfRaceEnrollmentAction;

    // Should skip for individual discipline with out-of-race
    expect($validator->shouldSkipInitialValidation($this->discipline))->toBeTrue();

    // Should not skip for relay discipline
    $relayDiscipline = Discipline::factory()->create(['enrollment_type' => 'relay']);
    expect($validator->shouldSkipInitialValidation($relayDiscipline))->toBeFalse();

    // Should not skip for individual discipline without out-of-race
    $regularDiscipline = Discipline::factory()->create(['enrollment_type' => 'individual']);
    expect($validator->shouldSkipInitialValidation($regularDiscipline))->toBeFalse();
});

test('isOutOfRace correctly identifies out-of-race status', function () {
    $validator = new ValidateOutOfRaceEnrollmentAction;

    expect($validator->isOutOfRace(
        [$this->outOfRaceAttribute->id => 'yes'],
        $this->outOfRaceAttribute->id
    ))->toBeTrue();

    expect($validator->isOutOfRace(
        [$this->outOfRaceAttribute->id => 'no'],
        $this->outOfRaceAttribute->id
    ))->toBeFalse();

    expect($validator->isOutOfRace(
        [],
        $this->outOfRaceAttribute->id
    ))->toBeFalse();
});
