<?php

use Domain\EventApplications\Actions\ValidateApplicationDatesAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns valid when end date is after start date', function () {
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute(
        now()->addDays(10)->format('Y-m-d'),
        now()->addDays(15)->format('Y-m-d')
    );

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

test('returns error when end date is before start date', function () {
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute(
        now()->addDays(15)->format('Y-m-d'),
        now()->addDays(10)->format('Y-m-d')
    );

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('End date must be after or equal to start date');
});

test('returns error when start date is in the past', function () {
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute(
        now()->subDays(5)->format('Y-m-d'),
        now()->addDays(5)->format('Y-m-d')
    );

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Start date cannot be in the past');
});

test('allows same start and end date', function () {
    $date = now()->addDays(10)->format('Y-m-d');
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute($date, $date);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

test('returns valid when only start date provided and is future', function () {
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute(now()->addDays(10)->format('Y-m-d'), null);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

test('returns valid when no dates provided', function () {
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute(null, null);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});

test('returns multiple errors when both validations fail', function () {
    $action = new ValidateApplicationDatesAction;
    $result = $action->execute(
        now()->subDays(5)->format('Y-m-d'),
        now()->subDays(10)->format('Y-m-d')
    );

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toHaveCount(2);
});
