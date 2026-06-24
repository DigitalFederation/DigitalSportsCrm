<?php

use Carbon\Carbon;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Models\License;

beforeEach(function () {
    $this->action = new CalculateLicenseValidityDatesAction;
});

it('calculates validity dates for weekly interval', function () {
    $license = License::factory()->create([
        'interval' => 2,
        'interval_unit' => 'weeks',
    ]);

    Carbon::setTestNow('2025-01-01');

    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-01-01')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2025-01-15');
});

it('calculates validity dates for monthly interval', function () {
    $license = License::factory()->create([
        'interval' => 3,
        'interval_unit' => 'months',
    ]);

    Carbon::setTestNow('2025-01-01');

    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-01-01')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2025-04-01');
});

it('calculates validity dates for yearly interval', function () {
    $license = License::factory()->create([
        'interval' => 1,
        'interval_unit' => 'years',
    ]);

    Carbon::setTestNow('2025-01-01');

    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-01-01')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2026-01-01');
});

it('uses custom start date when provided', function () {
    $license = License::factory()->create([
        'interval' => 6,
        'interval_unit' => 'months',
    ]);

    $dates = $this->action->execute($license, Carbon::parse('2025-03-15'));

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-03-15')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2025-09-15');
});

it('returns null end_date when license has no interval', function () {
    $license = License::factory()->create([
        'interval' => null,
        'interval_unit' => 'months',
    ]);

    Carbon::setTestNow('2025-01-01');
    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-01-01')
        ->and($dates['end_date'])->toBeNull();
});

it('returns null end_date when license has no interval unit', function () {
    $license = License::factory()->create([
        'interval' => 12,
        'interval_unit' => null,
    ]);

    Carbon::setTestNow('2025-01-01');
    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-01-01')
        ->and($dates['end_date'])->toBeNull();
});

it('calculates next term dates correctly', function () {
    $license = License::factory()->create([
        'interval' => 1,
        'interval_unit' => 'years',
    ]);

    Carbon::setTestNow('2025-12-31');
    $dates = $this->action->execute($license, Carbon::parse('2026-01-01'));

    expect($dates['start_date']->format('Y-m-d'))->toBe('2026-01-01')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2027-01-01');
});

it('handles leap years correctly for yearly licenses', function () {
    $license = License::factory()->create([
        'interval' => 1,
        'interval_unit' => 'years',
    ]);

    Carbon::setTestNow('2024-02-29'); // Leap year

    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2024-02-29')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2025-03-01'); // Carbon adds 1 year
});

it('handles month-end dates correctly', function () {
    $license = License::factory()->create([
        'interval' => 1,
        'interval_unit' => 'months',
    ]);

    Carbon::setTestNow('2025-01-31');

    $dates = $this->action->execute($license);

    expect($dates['start_date']->format('Y-m-d'))->toBe('2025-01-31')
        ->and($dates['end_date']->format('Y-m-d'))->toBe('2025-03-03'); // Carbon adds 1 month, adjusts for overflow

    // Test with a date that doesn't overflow
    Carbon::setTestNow('2025-01-15');
    $dates2 = $this->action->execute($license);

    expect($dates2['start_date']->format('Y-m-d'))->toBe('2025-01-15')
        ->and($dates2['end_date']->format('Y-m-d'))->toBe('2025-02-15'); // This works as expected
});
