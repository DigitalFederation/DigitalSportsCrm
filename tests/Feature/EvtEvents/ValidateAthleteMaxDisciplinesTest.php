<?php

use App\Enums\EvtAttributeTypesEnum;
use Domain\EvtEvents\Actions\ValidateAthleteMaxDisciplinesAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->competition = Competition::factory()->create([
        'event_id' => $this->event->id,
        'max_disciplines_per_athlete' => 2,
        'max_relays_per_athlete' => 1,
        'max_teams_per_athlete' => 1,
    ]);

    $this->individual = Individual::factory()->create();
});

test('allows enrollment when athlete has no existing disciplines', function () {
    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
    ]);

    $errorMessages = [];
    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages
    );

    expect($isValid)->toBeTrue();
    expect($errorMessages)->toBeEmpty();
});

test('allows enrollment when athlete is under the discipline limit', function () {
    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
    ]);

    // Create one existing enrollment
    AthleteEnrollment::factory()->create([
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'discipline_id' => Discipline::factory()->create(['enrollment_type' => 'individual'])->id,
    ]);

    $errorMessages = [];
    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages
    );

    expect($isValid)->toBeTrue();
    expect($errorMessages)->toBeEmpty();
});

test('prevents enrollment when athlete reaches individual discipline limit', function () {

    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
    ]);

    // Create attribute for out-of-race
    $outOfRaceAttribute = $discipline->attributes()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
        'name' => 'Out of Race',
        'attribute_data' => [
            'options' => ['yes' => 'Yes', 'no' => 'No'],
            'default_value' => 'no',
        ],
    ]);

    // Create two existing enrollments with in-race attributes
    for ($i = 0; $i < 2; $i++) {
        $existingDiscipline = Discipline::factory()->create(['enrollment_type' => 'individual']);
        $existingOutOfRaceAttribute = $existingDiscipline->attributes()->create([
            'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
            'name' => 'Out of Race',
            'attribute_data' => [
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default_value' => 'no',
            ],
        ]);

        $enrollment = AthleteEnrollment::factory()->create([
            'individual_id' => $this->individual->id,
            'event_id' => $this->event->id,
            'discipline_id' => $existingDiscipline->id,
        ]);

        // Create the attribute for the enrollment explicitly marking as in-race
        $enrollment->attributes()->create([
            'attribute_id' => $existingOutOfRaceAttribute->id,
            'value' => 'no',
        ]);
    }

    $errorMessages = [];
    $attributeValues = [$outOfRaceAttribute->id => 'no']; // Set new enrollment as in-race

    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages,
        $attributeValues,
    );

    expect($isValid)->toBeFalse();
    expect($errorMessages)->toContain('The selected athlete(s) have reached the maximum number of individual disciplines. Limit is  (2)');
});

test('allows unlimited disciplines when competition limit is null', function () {
    $this->competition->update(['max_disciplines_per_athlete' => null]);

    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
    ]);

    // Create multiple existing enrollments
    AthleteEnrollment::factory()->count(5)->create([
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'discipline_id' => Discipline::factory()->create(['enrollment_type' => 'individual'])->id,
    ]);

    $errorMessages = [];
    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages
    );

    expect($isValid)->toBeTrue();
    expect($errorMessages)->toBeEmpty();
});

test('prevents enrollment when athlete reaches relay limit', function () {

    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'relay',
    ]);

    // Create attribute for out-of-race
    $outOfRaceAttribute = $discipline->attributes()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
        'name' => 'Out of Race',
        'attribute_data' => [
            'options' => ['yes' => 'Yes', 'no' => 'No'],
            'default_value' => 'no',
        ],
    ]);

    // Create two existing enrollments with in-race attributes
    for ($i = 0; $i < 2; $i++) {
        $existingDiscipline = Discipline::factory()->create(['enrollment_type' => 'relay']);
        $existingOutOfRaceAttribute = $existingDiscipline->attributes()->create([
            'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
            'name' => 'Out of Race',
            'attribute_data' => [
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default_value' => 'no',
            ],
        ]);

        $enrollment = AthleteEnrollment::factory()->create([
            'individual_id' => $this->individual->id,
            'event_id' => $this->event->id,
            'discipline_id' => $existingDiscipline->id,
        ]);

        // Create the attribute for the enrollment explicitly marking as in-race
        $enrollment->attributes()->create([
            'attribute_id' => $existingOutOfRaceAttribute->id,
            'value' => 'no',
        ]);
    }

    $errorMessages = [];
    $attributeValues = [$outOfRaceAttribute->id => 'no']; // Set new enrollment as in-race

    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages,
        $attributeValues,
    );

    expect($isValid)->toBeFalse();
    expect($errorMessages)->toContain('The selected athlete(s) have reached the maximum number of relay disciplines. Limit is (1)');
});

test('prevents enrollment when athlete reaches team limit', function () {

    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'team',
    ]);

    // Create attribute for out-of-race
    $outOfRaceAttribute = $discipline->attributes()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
        'name' => 'Out of Race',
        'attribute_data' => [
            'options' => ['yes' => 'Yes', 'no' => 'No'],
            'default_value' => 'no',
        ],
    ]);

    // Create two existing enrollments with in-race attributes
    for ($i = 0; $i < 2; $i++) {
        $existingDiscipline = Discipline::factory()->create(['enrollment_type' => 'team']);
        $existingOutOfRaceAttribute = $existingDiscipline->attributes()->create([
            'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
            'name' => 'Out of Race',
            'attribute_data' => [
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default_value' => 'no',
            ],
        ]);

        $enrollment = AthleteEnrollment::factory()->create([
            'individual_id' => $this->individual->id,
            'event_id' => $this->event->id,
            'discipline_id' => $existingDiscipline->id,
        ]);

        // Create the attribute for the enrollment explicitly marking as in-race
        $enrollment->attributes()->create([
            'attribute_id' => $existingOutOfRaceAttribute->id,
            'value' => 'no',
        ]);
    }

    $errorMessages = [];
    $attributeValues = [$outOfRaceAttribute->id => 'no']; // Set new enrollment as in-race

    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages,
        $attributeValues,
    );

    expect($isValid)->toBeFalse();
    expect($errorMessages)->toContain('The selected athlete(s) have reached the maximum number of team disciplines. Limit is (1)');
});

test('allows out-of-race enrollment even when athlete reached discipline limit', function () {
    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
    ]);

    // Create attribute for out-of-race
    $attribute = $discipline->attributes()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
        'name' => 'Out of Race',
    ]);

    // Create two existing enrollments (reaching the limit)
    AthleteEnrollment::factory()->count(2)->create([
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'discipline_id' => Discipline::factory()->create(['enrollment_type' => 'individual'])->id,
    ]);

    $errorMessages = [];
    $attributeValues = [$attribute->id => 'yes']; // Mark as out-of-race

    $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
        $this->competition,
        $this->individual->id,
        $discipline,
        $errorMessages,
        $attributeValues
    );

    expect($isValid)->toBeTrue();
    expect($errorMessages)->toBeEmpty();
});
