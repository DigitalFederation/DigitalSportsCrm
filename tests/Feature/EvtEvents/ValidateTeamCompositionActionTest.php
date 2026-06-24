<?php

use App\Enums\EvtDisciplineEnrollmentTypeEnum;
use Domain\EvtEvents\Actions\ValidateTeamCompositionAction;
use Domain\EvtEvents\Models\Discipline;

test('fails validation when discipline requires 2 male and 2 female, but given 3 male and 1 female', function () {
    // 1) Create a "relay" discipline with team composition requirements
    $discipline = Discipline::factory()->create([
        'enrollment_type' => EvtDisciplineEnrollmentTypeEnum::relay->value,
        'team_composition_requirements' => [
            'male' => 2,
            'female' => 2,
        ],
    ]);

    // 2) Simulate selection of 3 male and 1 female
    $selectedIndividuals = [
        ['id' => 1, 'gender' => 'male'],
        ['id' => 2, 'gender' => 'male'],
        ['id' => 3, 'gender' => 'male'],
        ['id' => 4, 'gender' => 'female'],
    ];

    // 3) Run the validation
    $errorMessages = [];
    $isValid = (new ValidateTeamCompositionAction)->execute(
        $discipline,
        $selectedIndividuals,
        $errorMessages
    );

    // 4) Assert that validation fails and a helpful error is returned
    expect($isValid)->toBeFalse();
    expect($errorMessages)->toContain('Relay team requires exactly 2 male participants, got 3.');
});

test('passes validation when discipline requires 2 male and 2 female, and we provide exactly that', function () {
    // 1) Same discipline requirements
    $discipline = Discipline::factory()->create([
        'enrollment_type' => EvtDisciplineEnrollmentTypeEnum::relay->value,
        'team_composition_requirements' => [
            'male' => 2,
            'female' => 2,
        ],
    ]);

    // 2) Provide correct distribution: 2 male, 2 female
    $selectedIndividuals = [
        ['id' => 1, 'gender' => 'male'],
        ['id' => 2, 'gender' => 'male'],
        ['id' => 3, 'gender' => 'female'],
        ['id' => 4, 'gender' => 'female'],
    ];

    // 3) Execute validation
    $errorMessages = [];
    $isValid = (new ValidateTeamCompositionAction)->execute(
        $discipline,
        $selectedIndividuals,
        $errorMessages
    );

    // 4) This time it must pass (no error messages)
    expect($isValid)->toBeTrue();
    expect($errorMessages)->toBeEmpty();
});

test('passes validation when discipline requires 4 male and 0 female, and exactly 4 male provided', function () {
    // 1) Create discipline requiring 4 male, 0 female
    $discipline = Discipline::factory()->create([
        'enrollment_type' => EvtDisciplineEnrollmentTypeEnum::relay->value,
        'team_composition_requirements' => [
            'male' => 4,
            'female' => 0,
        ],
    ]);

    // 2) Provide exactly 4 male participants
    $selectedIndividuals = [
        ['id' => 1, 'gender' => 'male'],
        ['id' => 2, 'gender' => 'male'],
        ['id' => 3, 'gender' => 'male'],
        ['id' => 4, 'gender' => 'male'],
    ];

    $errorMessages = [];
    $isValid = (new ValidateTeamCompositionAction)->execute(
        $discipline,
        $selectedIndividuals,
        $errorMessages
    );

    // 3) Should pass with no error
    expect($isValid)->toBeTrue();
    expect($errorMessages)->toBeEmpty();
});

test('fails validation when discipline requires 4 male and 0 female, but only 3 male provided', function () {
    // 1) Same discipline (4 male, 0 female)
    $discipline = Discipline::factory()->create([
        'enrollment_type' => EvtDisciplineEnrollmentTypeEnum::relay->value,
        'team_composition_requirements' => [
            'male' => 4,
            'female' => 0,
        ],
    ]);

    // 2) Try enrolling only 3 male participants
    $selectedIndividuals = [
        ['id' => 1, 'gender' => 'male'],
        ['id' => 2, 'gender' => 'male'],
        ['id' => 3, 'gender' => 'male'],
    ];

    $errorMessages = [];
    $isValid = (new ValidateTeamCompositionAction)->execute(
        $discipline,
        $selectedIndividuals,
        $errorMessages
    );

    // 3) Should fail; check that the error message complains about missing 1 male
    expect($isValid)->toBeFalse();
    expect($errorMessages)->toContain('Relay team requires exactly 4 participants (4 male, 0 female).');
});
