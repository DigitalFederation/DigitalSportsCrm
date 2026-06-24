<?php

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Individuals\Models\Individual;

describe('DivingProfessionalCertification Factory', function () {
    test('creates certification with default values', function () {
        $certification = DivingProfessionalCertification::factory()->create();

        expect($certification)->toBeInstanceOf(DivingProfessionalCertification::class)
            ->and($certification->individual_id)->not->toBeNull()
            ->and($certification->certification_name)->not->toBeNull()
            ->and($certification->certification_system)->not->toBeNull()
            ->and($certification->certification_level)->not->toBeNull()
            ->and($certification->certification_number)->not->toBeNull()
            ->and($certification->issue_date)->not->toBeNull()
            ->and($certification->status_class)->toBe(PendingValidationDivingCertificationState::class);
    });

    test('creates certification with custom attributes', function () {
        $individual = Individual::factory()->create();

        $certification = DivingProfessionalCertification::factory()->create([
            'individual_id' => $individual->id,
            'certification_name' => 'Custom Instructor',
            'certification_system' => 'SSI',
            'certification_level' => 'Advanced Instructor',
            'certification_number' => 'SSI-CUSTOM-123',
            'national_equivalency' => 'N3',
        ]);

        expect($certification->individual_id)->toBe($individual->id)
            ->and($certification->certification_name)->toBe('Custom Instructor')
            ->and($certification->certification_system)->toBe('SSI')
            ->and($certification->certification_level)->toBe('Advanced Instructor')
            ->and($certification->certification_number)->toBe('SSI-CUSTOM-123')
            ->and($certification->national_equivalency)->toBe('N3');
    });

    test('creates certification with different systems', function () {
        $systems = ['SSI', 'PADI', 'SDI_TDI', 'DDI', 'GUE', 'CMAS'];

        foreach ($systems as $system) {
            $certification = DivingProfessionalCertification::factory()->create([
                'certification_system' => $system,
            ]);

            expect($certification->certification_system)->toBe($system);
        }
    });

    test('creates certification with expiration date', function () {
        $expirationDate = now()->addYear();

        $certification = DivingProfessionalCertification::factory()->create([
            'expiration_date' => $expirationDate,
        ]);

        expect($certification->expiration_date->toDateString())
            ->toBe($expirationDate->toDateString());
    });

    test('creates certification without expiration date', function () {
        $certification = DivingProfessionalCertification::factory()->create([
            'expiration_date' => null,
        ]);

        expect($certification->expiration_date)->toBeNull();
    });

    test('factory generates unique certification numbers', function () {
        $cert1 = DivingProfessionalCertification::factory()->create();
        $cert2 = DivingProfessionalCertification::factory()->create();

        expect($cert1->certification_number)
            ->not->toBe($cert2->certification_number);
    });

    test('factory creates realistic issue dates', function () {
        $certification = DivingProfessionalCertification::factory()->create();

        expect($certification->issue_date)
            ->toBeLessThanOrEqual(now())
            ->and($certification->issue_date)
            ->toBeGreaterThan(now()->subYears(10));
    });
});
