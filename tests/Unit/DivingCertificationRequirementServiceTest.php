<?php

use App\Services\DivingCertificationRequirementService;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DivingCertificationRequirementService;

    // Create a test license
    $this->license = License::factory()->create();

    // Seed requirements for this test license
    DB::table('license_required_certifications')->insert([
        [
            'license_id' => $this->license->id,
            'certification_id' => null,
            'requester_type' => 'technical_director',
            'certification_level' => 'diver_level_3',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'license_id' => $this->license->id,
            'certification_id' => null,
            'requester_type' => 'technical_director',
            'certification_level' => 'instructor_level_1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'license_id' => $this->license->id,
            'certification_id' => null,
            'requester_type' => 'technical_director',
            'certification_level' => 'instructor_level_2',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'license_id' => $this->license->id,
            'certification_id' => null,
            'requester_type' => 'technical_director',
            'certification_level' => 'instructor_level_3',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    // Create a test individual
    $this->individual = Individual::factory()->create();
});

test('it can get required certification levels for a license', function () {
    $requiredLevels = $this->service->getRequiredCertificationLevels($this->license);

    // Should have the requirements we seeded
    expect($requiredLevels)->toContain('diver_level_3')
        ->and($requiredLevels)->toContain('instructor_level_1')
        ->and($requiredLevels)->toContain('instructor_level_2')
        ->and($requiredLevels)->toContain('instructor_level_3');
});

test('it correctly identifies when individual meets certification requirements', function () {
    // Create an active diving certification with diver_level_3
    DivingProfessionalCertification::factory()->create([
        'individual_id' => $this->individual->id,
        'national_equivalency' => 'diver_level_3',
        'status_class' => ActiveDivingCertificationState::class,
    ]);

    $meetsCertification = $this->service->individualMeetsCertificationRequirements(
        $this->individual,
        $this->license
    );

    expect($meetsCertification)->toBeTrue();
});

test('it correctly identifies when individual does not meet certification requirements', function () {
    // Create an active diving certification with a level not required for this license
    DivingProfessionalCertification::factory()->create([
        'individual_id' => $this->individual->id,
        'national_equivalency' => 'first_aid_bls_oxygen',
        'status_class' => ActiveDivingCertificationState::class,
    ]);

    $meetsCertification = $this->service->individualMeetsCertificationRequirements(
        $this->individual,
        $this->license
    );

    expect($meetsCertification)->toBeFalse();
});

test('it returns missing certification levels', function () {
    // Create an individual with instructor_level_1 only
    DivingProfessionalCertification::factory()->create([
        'individual_id' => $this->individual->id,
        'national_equivalency' => 'instructor_level_1',
        'status_class' => ActiveDivingCertificationState::class,
    ]);

    $missingLevels = $this->service->getMissingCertificationLevels(
        $this->individual,
        $this->license
    );

    // Should be missing diver_level_3, instructor_level_2, instructor_level_3
    expect($missingLevels)->toContain('diver_level_3')
        ->and($missingLevels)->toContain('instructor_level_2')
        ->and($missingLevels)->toContain('instructor_level_3')
        ->and($missingLevels)->not()->toContain('instructor_level_1'); // This one is already possessed
});

test('it provides display names for certification levels', function () {
    $displayNames = $this->service->getCertificationLevelDisplayNames();

    expect($displayNames)->toHaveKey('diver_level_3')
        ->and($displayNames)->toHaveKey('instructor_level_1')
        ->and($displayNames)->toHaveKey('instructor_level_2')
        ->and($displayNames)->toHaveKey('instructor_level_3')
        ->and($displayNames)->toHaveKey('compressor_operator');
});

test('it generates formatted requirements text', function () {
    $formattedText = $this->service->getFormattedRequirementsText($this->license);

    expect($formattedText)->toBeString()
        ->and($formattedText)->toContain('Certificações obrigatórias'); // Portuguese version
});

test('it does not approve individual with wrong national certification level', function () {
    // Create an individual with a diving professional certification but wrong national equivalent level
    DivingProfessionalCertification::factory()->create([
        'individual_id' => $this->individual->id,
        'national_equivalency' => 'first_aid_bls_oxygen', // Wrong level - not one of the required levels
        'status_class' => ActiveDivingCertificationState::class,
    ]);

    // This should return false because the individual doesn't have any of the required national certification levels:
    // (diver_level_3, instructor_level_1, instructor_level_2, instructor_level_3)
    // They only have 'first_aid_bls_oxygen' which is not in the required list
    $meetsCertification = $this->service->individualMeetsCertificationRequirements(
        $this->individual,
        $this->license
    );

    expect($meetsCertification)->toBeFalse('Individual should not meet requirements with wrong national certification level');
});
