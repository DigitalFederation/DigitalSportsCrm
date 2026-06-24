<?php

use Domain\Individuals\Actions\DetectIfIndividualIsInstructorAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;

it('check if an individual is a Technical Official', function () {

    $technicalOfficialRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $license = License::factory()->create(['professional_role_id' => $technicalOfficialRole->id]);
    $individual = Individual::factory()->create();
    $activeLicense = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $action = new DetectIfIndividualIsInstructorAction;
    $individualQuery = Individual::query()->where('id', $individual->id);

    expect($action($individualQuery, null, 'TECHNICAL_OFFICIAL'))->toBeTrue();
});
