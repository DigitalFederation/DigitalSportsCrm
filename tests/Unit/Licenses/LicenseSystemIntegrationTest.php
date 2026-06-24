<?php

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\ActiveLicenseAttributedState;

it('correctly calculates license prices with tax', function () {
    $calculatePriceAction = new \Domain\Licenses\Actions\CalculateLicensePriceAction;

    // Test individual license with tax
    $license = License::factory()->create([
        'unit_value_individual' => 100,
        'tax_percentage' => 21,
    ]);

    $price = $calculatePriceAction($license, Individual::class);
    expect($price)->toBe(121.0); // 100 + 21% tax

    // Test entity license without tax
    $license = License::factory()->create([
        'unit_value_entity' => 200,
        'tax_percentage' => null,
    ]);

    $price = $calculatePriceAction($license, Entity::class);
    expect($price)->toBe(200.0);
});

it('handles license management actions correctly', function () {
    // Create an active license
    $licenseAttributed = \Domain\Licenses\Models\LicenseAttributed::factory()->create([
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $manageAction = new \Domain\Licenses\Actions\ManageLicenseAction(
        new \Domain\Licenses\States\ActiveToSuspendedTransition,
        new \Domain\Licenses\States\SuspendedToActiveTransition
    );

    // Test suspension
    $suspendedLicense = $manageAction->suspend($licenseAttributed, 'Test suspension');
    expect($suspendedLicense->status_class)->toBe(\Domain\Licenses\States\SuspendedLicenseAttributedState::class);

    // Test reactivation
    $reactivatedLicense = $manageAction->reactivate($suspendedLicense);
    expect($reactivatedLicense->status_class)->toBe(ActiveLicenseAttributedState::class);

    // Test notes
    $licenseWithNotes = $manageAction->addNotes($reactivatedLicense, 'Test admin note');
    expect($licenseWithNotes->notes)->toContain('Test admin note');
});
