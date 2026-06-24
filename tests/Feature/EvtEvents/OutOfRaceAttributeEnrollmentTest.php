<?php

use Domain\EvtEvents\Actions\ValidateAttributeRulesAction;
use Domain\EvtEvents\Actions\ValidateOutOfRaceEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\AthleteEnrollmentAttributes;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required entities
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create();
    $this->discipline = Discipline::factory()->create();
    $this->individual1 = Individual::factory()->create();
    $this->individual2 = Individual::factory()->create();
    $this->individual3 = Individual::factory()->create();

    // Create the "out of race" attribute
    $this->outOfRaceAttribute = Attribute::create([
        'name' => 'out_of_race',
        'attribute_type' => 'select',
        'attribute_data' => [
            'options' => ['yes' => 'Yes', 'no' => 'No'],
        ],
        'default_value' => 'no',
    ]);

    // Create the rule to allow only 2 athletes without "out of race"
    AttributeRules::create([
        'name' => 'Max 2 Athletes',
        'attribute_id' => $this->outOfRaceAttribute->id,
        'operator' => 'max',
        'comparison_value' => 2,
        'is_validation' => true,
    ]);

    // Create an enrollment for the federation
    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    // Create the attribute validation action
    $this->validateAttributeRulesAction = new ValidateAttributeRulesAction;
});

it('successfully enrolls 2 athletes without "out of race" and others with "out of race"', function () {
    // Create enrollments for the first two athletes without "out of race"
    $athleteEnrollment1 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    $athleteEnrollment2 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual2->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment1->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment2->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    // Create the third athlete enrollment with "out of race" set to "yes"
    $athleteEnrollment3 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual3->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment3->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'yes',
    ]);

    // Assertions to check if enrollments are created correctly
    $this->assertDatabaseHas('evt_athletes_enrollment_attributes', [
        'athlete_enrollment_id' => $athleteEnrollment1->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    $this->assertDatabaseHas('evt_athletes_enrollment_attributes', [
        'athlete_enrollment_id' => $athleteEnrollment2->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    $this->assertDatabaseHas('evt_athletes_enrollment_attributes', [
        'athlete_enrollment_id' => $athleteEnrollment3->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'yes',
    ]);
});

it('fails to enroll more than 2 athletes without "out of race"', function () {
    // Create enrollments for the first two athletes without "out of race"
    $athleteEnrollment1 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    $athleteEnrollment2 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual2->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment1->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment2->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);

    $existingAttributes = [
        $this->outOfRaceAttribute->id => ['no', 'no'],
    ];

    $newAttributes = [
        $this->outOfRaceAttribute->id => ['no'],
    ];

    DB::beginTransaction();

    try {
        $errors = $this->validateAttributeRulesAction->validateBatchAttributes($newAttributes, $existingAttributes);
        if (! empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        $athleteEnrollment3 = AthleteEnrollment::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'individual_id' => $this->individual3->id,
            'event_id' => $this->event->id,
            'federation_id' => $this->federation->id,
            'discipline_id' => $this->discipline->id,
        ]);

        AthleteEnrollmentAttributes::create([
            'athlete_enrollment_id' => $athleteEnrollment3->id,
            'attribute_id' => $this->outOfRaceAttribute->id,
            'value' => 'no',
        ]);

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        // Ensure the exception is caught and the third enrollment is not created
        expect($e->getMessage())->toBeString();
    }

    // Ensure the third enrollment with "no" value should not have been created
    $this->assertDatabaseMissing('evt_athletes_enrollment_attributes', [
        'athlete_enrollment_id' => $athleteEnrollment3->id ?? null,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'no',
    ]);
});

it('allows enrolling out-of-race athlete after reaching in-race limit', function () {
    // Set up dependencies
    $this->discipline->update(['athlete_limit' => 4, 'enrollment_type' => 'individual']);

    // Create validation action
    $validationAction = new ValidateOutOfRaceEnrollmentAction;

    // Create 4 existing in-race athletes
    for ($i = 0; $i < 4; $i++) {
        $athleteEnrollment = AthleteEnrollment::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'individual_id' => Individual::factory()->create()->id,
            'event_id' => $this->event->id,
            'federation_id' => $this->federation->id,
            'discipline_id' => $this->discipline->id,
        ]);

        AthleteEnrollmentAttributes::create([
            'athlete_enrollment_id' => $athleteEnrollment->id,
            'attribute_id' => $this->outOfRaceAttribute->id,
            'value' => 'no',
        ]);
    }

    // Attempt to create out-of-race enrollment
    $errorMessages = [];
    $attributeValues = [
        $this->outOfRaceAttribute->id => 'yes',
    ];

    $result = $validationAction->execute(
        $this->discipline,
        $this->event->id,
        $this->federation,
        [$attributeValues],
        $errorMessages
    );

    expect($result)->toBeTrue()
        ->and($errorMessages)->toBeEmpty();

    // Verify enrollment can be created
    $athleteEnrollment5 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => Individual::factory()->create()->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment5->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'yes',
    ]);

    $this->assertDatabaseHas('evt_athletes_enrollment_attributes', [
        'athlete_enrollment_id' => $athleteEnrollment5->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'yes',
    ]);
});

it('allows enrolling out-of-race athletes when in-race limit is reached', function () {
    // Set discipline limit to 2
    $this->discipline->update(['athlete_limit' => 2]);

    // Create two existing in-race athletes
    for ($i = 0; $i < 2; $i++) {
        $athleteEnrollment = AthleteEnrollment::factory()->create([
            'enrollment_id' => $this->enrollment->id,
            'individual_id' => Individual::factory()->create()->id,
            'event_id' => $this->event->id,
            'federation_id' => $this->federation->id,
            'discipline_id' => $this->discipline->id,
        ]);

        AthleteEnrollmentAttributes::create([
            'athlete_enrollment_id' => $athleteEnrollment->id,
            'attribute_id' => $this->outOfRaceAttribute->id,
            'value' => 'no',
        ]);
    }

    // Attempt to create an out-of-race enrollment
    $errorMessages = [];
    $attributeValues = [
        $this->outOfRaceAttribute->id => 'yes',
    ];

    $result = (new ValidateOutOfRaceEnrollmentAction)->execute(
        $this->discipline,
        $this->event->id,
        $this->federation,
        [$attributeValues],
        $errorMessages
    );

    // Assert validation passes
    expect($result)->toBeTrue()
        ->and($errorMessages)->toBeEmpty();

    // Verify enrollment can be created
    $athleteEnrollment3 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => Individual::factory()->create()->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
    ]);

    AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment3->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'yes',
    ]);

    $this->assertDatabaseHas('evt_athletes_enrollment_attributes', [
        'athlete_enrollment_id' => $athleteEnrollment3->id,
        'attribute_id' => $this->outOfRaceAttribute->id,
        'value' => 'yes',
    ]);
});
