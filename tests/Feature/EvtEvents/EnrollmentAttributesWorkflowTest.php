<?php

use App\Enums\EvtAttributeTypesEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use App\Models\User;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\AthleteEnrollmentAttributes;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Creates necessary test environment for enrollment tests
 */
function createBaseEnrollment()
{
    // Create a user
    $user = User::factory()->create();

    // Create federation and event
    $federation = Federation::factory()->create();
    $event = Event::factory()->create();
    $individual = Individual::factory()->create();

    // Create base enrollment
    $enrollment = Enrollment::create([
        'event_id' => $event->id,
        'enrollable_id' => $federation->id,
        'enrollable_type' => get_class($federation),
        'status_class' => EvtEnrollmentStatusEnum::ACTIVE->value,
        'user_id' => $user->id,
    ]);

    return [
        'user' => $user,
        'federation' => $federation,
        'event' => $event,
        'individual' => $individual,
        'enrollment' => $enrollment,
    ];
}

/**
 * This test suite validates the complete workflow for enrollment attributes,
 * from creation to retrieval and display, focusing on the structure of attribute data
 * and options handling.
 */
it('completes the full enrollment attribute workflow', function () {
    // Set up base enrollment environment
    $env = createBaseEnrollment();

    // 1. Create a discipline with an attribute that has numeric options
    $discipline = Discipline::factory()->create([
        'name' => 'Relay Test Discipline',
        'enrollment_type' => 'relay',
    ]);

    $relay_order_attribute = Attribute::create([
        'name' => 'Relay Order',
        'attribute_type' => EvtAttributeTypesEnum::SELECT->value,
        'fillable_global' => false,
        'attribute_data' => [
            0 => '1',
            1 => '2',
            2 => '3',
        ],
    ]);

    $discipline->attributes()->attach($relay_order_attribute);

    // 3. Create an enrollment with attributes, including the required enrollment_id
    $athleteEnrollment = AthleteEnrollment::create([
        'event_id' => $env['event']->id,
        'individual_id' => $env['individual']->id,
        'discipline_id' => $discipline->id,
        'federation_id' => $env['federation']->id,
        'enrollment_id' => $env['enrollment']->id,
        'status_class' => \App\Enums\EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    // 4. Retrieve attributes using GetAttributesAndRulesFromDisciplineAction
    $action = new GetAttributesAndRulesFromDisciplineAction;
    $attributes = $action->execute($discipline->id);

    // 5. Validate the structure of the returned attributes
    expect($attributes)->toHaveKeys(['attributes', 'global_attributes']);
    expect($attributes['attributes'])->toHaveCount(1);

    $attributeData = $attributes['attributes'][0]['attribute_data'];
    expect($attributeData['name'])->toBe('Relay Order');

    // Validate that options are properly structured for dropdown
    expect($attributeData['options'])->toBeArray();
    expect($attributeData['options'])->toHaveCount(3);
    expect($attributeData['options'])->toHaveKey('1');
    expect($attributeData['options'])->toHaveKey('2');
    expect($attributeData['options'])->toHaveKey('3');
    expect($attributeData['options']['1'])->toBe('1');
    expect($attributeData['options']['2'])->toBe('2');
    expect($attributeData['options']['3'])->toBe('3');

    // 6. Create attribute values for the enrollment
    $enrollmentAttribute = AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment->id,
        'attribute_id' => $relay_order_attribute->id,
        'value' => '2', // Selecting the second option
    ]);

    // 7. Retrieve and validate the attribute value
    $savedAttribute = AthleteEnrollmentAttributes::find($enrollmentAttribute->id);
    expect($savedAttribute->value)->toBe('2');

    // 8. Re-retrieve the attributes to ensure nothing changed
    $refreshedAttributes = $action->execute($discipline->id);
    $refreshedAttributeData = $refreshedAttributes['attributes'][0]['attribute_data'];

    // Options should still be correctly structured
    expect($refreshedAttributeData['options'])->toBeArray();
    expect($refreshedAttributeData['options'])->toHaveCount(3);
    expect($refreshedAttributeData['options'])->toHaveKey('1');
    expect($refreshedAttributeData['options'])->toHaveKey('2');
    expect($refreshedAttributeData['options'])->toHaveKey('3');
    expect($refreshedAttributeData['options']['1'])->toBe('1');
    expect($refreshedAttributeData['options']['2'])->toBe('2');
    expect($refreshedAttributeData['options']['3'])->toBe('3');
});

it('handles out-of-race attributes correctly', function () {
    // Set up base enrollment environment
    $env = createBaseEnrollment();

    // 1. Create a discipline with an out-of-race attribute
    $discipline = Discipline::factory()->create([
        'name' => 'Standard Discipline',
    ]);

    $outOfRaceAttribute = Attribute::create([
        'name' => 'Competition Status',
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE->value,
        'fillable_global' => false,
        'attribute_data' => [
            'options' => [
                'yes' => 'Out of Competition',
                'no' => 'Official Competitor',
            ],
            'default_value' => 'no',
        ],
    ]);

    $discipline->attributes()->attach($outOfRaceAttribute);

    // 3. Create an enrollment with attributes, including the required enrollment_id
    $athleteEnrollment = AthleteEnrollment::create([
        'event_id' => $env['event']->id,
        'individual_id' => $env['individual']->id,
        'discipline_id' => $discipline->id,
        'federation_id' => $env['federation']->id,
        'enrollment_id' => $env['enrollment']->id,
        'status_class' => \App\Enums\EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    // 4. Retrieve attributes using GetAttributesAndRulesFromDisciplineAction
    $action = new GetAttributesAndRulesFromDisciplineAction;
    $attributes = $action->execute($discipline->id);

    // 5. Validate the structure of the returned attributes
    $attributeData = $attributes['attributes'][0]['attribute_data'];
    expect($attributeData['name'])->toBe('Competition Status');
    expect($attributeData['type'])->toBe(EvtAttributeTypesEnum::OUTOFRACE->value);

    // Validate that options are properly structured - using individual assertions instead of array comparison
    expect($attributeData['options'])->toBeArray();
    expect($attributeData['options'])->toHaveCount(2);
    expect($attributeData['options'])->toHaveKey('yes');
    expect($attributeData['options'])->toHaveKey('no');
    expect($attributeData['options']['yes'])->toBe('Out of Competition');
    expect($attributeData['options']['no'])->toBe('Official Competitor');

    // Default value should be 'no' (Official Competitor)
    expect($attributeData['default_value'])->toBe('no');

    // 6. Create attribute values for the enrollment
    $enrollmentAttribute = AthleteEnrollmentAttributes::create([
        'athlete_enrollment_id' => $athleteEnrollment->id,
        'attribute_id' => $outOfRaceAttribute->id,
        'value' => 'yes', // Setting to Out of Competition
    ]);

    // 7. Retrieve and validate the attribute value
    $savedAttribute = AthleteEnrollmentAttributes::find($enrollmentAttribute->id);
    expect($savedAttribute->value)->toBe('yes');
});

it('handles mixed attribute data formats correctly', function () {
    // Set up base enrollment environment
    $env = createBaseEnrollment();

    // 1. Create a discipline with mixed attribute data formats
    $discipline = Discipline::factory()->create();

    $mixedAttribute = Attribute::create([
        'name' => 'Mixed Format Attribute',
        'attribute_type' => EvtAttributeTypesEnum::SELECT->value,
        'fillable_global' => false,
        'attribute_data' => [
            'options' => ['a' => 'Option A'],
            'required' => true,
            0 => 'Value 1',
            1 => 'Value 2',
        ],
    ]);

    $discipline->attributes()->attach($mixedAttribute);

    // 2. Retrieve and validate the attributes
    $action = new GetAttributesAndRulesFromDisciplineAction;
    $attributes = $action->execute($discipline->id);

    $attributeData = $attributes['attributes'][0]['attribute_data'];

    // The explicit options should be preserved
    expect($attributeData['options'])->toBeArray();
    expect($attributeData['options'])->toHaveKey('a');
    expect($attributeData['options']['a'])->toBe('Option A');

    // The required flag should also be preserved
    expect($attributeData['required'])->toBeTrue();
});
