<?php

use Domain\Licenses\Actions\ManageLicenseAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ActiveToSuspendedTransition;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Domain\Licenses\States\SuspendedToActiveTransition;

it('can suspend an active license', function () {
    // Create test license
    $license = LicenseAttributed::factory()->create([
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $manageLicenseAction = new ManageLicenseAction(
        new ActiveToSuspendedTransition,
        new SuspendedToActiveTransition
    );

    $result = $manageLicenseAction->suspend($license, 'Test suspension');

    expect($result->status_class)->toBe(SuspendedLicenseAttributedState::class);
});

it('can reactivate a suspended license', function () {
    // Create test license
    $license = LicenseAttributed::factory()->create([
        'status_class' => SuspendedLicenseAttributedState::class,
    ]);

    $manageLicenseAction = new ManageLicenseAction(
        new ActiveToSuspendedTransition,
        new SuspendedToActiveTransition
    );

    $result = $manageLicenseAction->reactivate($license);

    expect($result->status_class)->toBe(ActiveLicenseAttributedState::class);
});

it('can add notes to a license', function () {
    $license = LicenseAttributed::factory()->create(['notes' => null]);

    $manageLicenseAction = new ManageLicenseAction(
        new ActiveToSuspendedTransition,
        new SuspendedToActiveTransition
    );

    $result = $manageLicenseAction->addNotes($license, 'Test admin note');

    expect($result->notes)->toContain('Test admin note');
});

it('throws exception when trying to suspend non-active license', function () {
    $license = LicenseAttributed::factory()->create([
        'status_class' => SuspendedLicenseAttributedState::class,
    ]);

    $manageLicenseAction = new ManageLicenseAction(
        new ActiveToSuspendedTransition,
        new SuspendedToActiveTransition
    );

    expect(fn () => $manageLicenseAction->suspend($license))
        ->toThrow(Exception::class, 'Only active licenses can be suspended');
});
