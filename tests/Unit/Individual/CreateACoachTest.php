<?php

use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;

it('check if an individual is a Coach', function () {

    $coachRole = ProfessionalRole::factory()->create(['role' => 'COACH']);
    $license = License::factory()->create(['professional_role_id' => $coachRole->id]);

    $individual = Individual::factory()->create();
    $activeLicense = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    expect($individual->isCoach())->toBeTrue();

});

it('check if an individual isn\'t a Coach', function () {
    $nonCoachRole = ProfessionalRole::factory()->create(['role' => 'OTHER_ROLE']);
    $license = License::factory()->create(['professional_role_id' => $nonCoachRole->id]);

    $individual = Individual::factory()->create();
    $activeLicense = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    expect($individual->isCoach())->toBeFalse();
});
