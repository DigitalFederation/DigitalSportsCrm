<?php

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\ExpiredDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Diving\States\RevokedDivingCertificationState;

describe('DivingCertificationState Classes', function () {
    test('PendingValidationDivingCertificationState methods work correctly', function () {
        $certification = new DivingProfessionalCertification;
        $state = new PendingValidationDivingCertificationState($certification);

        expect($state->canBeValidated())->toBeTrue()
            ->and($state->canBeRevoked())->toBeFalse()
            ->and($state->isActive())->toBeFalse()
            ->and($state->name())->toBeString()
            ->and($state->color())->toBeString();
    });

    test('ActiveDivingCertificationState methods work correctly', function () {
        $certification = new DivingProfessionalCertification;
        $state = new ActiveDivingCertificationState($certification);

        expect($state->canBeValidated())->toBeFalse()
            ->and($state->canBeRevoked())->toBeTrue()
            ->and($state->isActive())->toBeTrue()
            ->and($state->name())->toBeString()
            ->and($state->color())->toBeString();
    });

    test('ExpiredDivingCertificationState methods work correctly', function () {
        $certification = new DivingProfessionalCertification;
        $state = new ExpiredDivingCertificationState($certification);

        expect($state->canBeValidated())->toBeFalse()
            ->and($state->canBeRevoked())->toBeFalse()
            ->and($state->isActive())->toBeFalse()
            ->and($state->name())->toBeString()
            ->and($state->color())->toBeString();
    });

    test('RevokedDivingCertificationState methods work correctly', function () {
        $certification = new DivingProfessionalCertification;
        $state = new RevokedDivingCertificationState($certification);

        expect($state->canBeValidated())->toBeFalse()
            ->and($state->canBeRevoked())->toBeFalse()
            ->and($state->isActive())->toBeFalse()
            ->and($state->name())->toBeString()
            ->and($state->color())->toBeString();
    });

    test('all states have proper color coding', function () {
        $certification = new DivingProfessionalCertification;

        $pendingState = new PendingValidationDivingCertificationState($certification);
        $activeState = new ActiveDivingCertificationState($certification);
        $expiredState = new ExpiredDivingCertificationState($certification);
        $revokedState = new RevokedDivingCertificationState($certification);

        $colors = [
            $pendingState->color(),
            $activeState->color(),
            $expiredState->color(),
            $revokedState->color(),
        ];

        // Each state should have a unique color
        expect(array_unique($colors))->toHaveCount(4);

        // Colors should be valid hex colors
        foreach ($colors as $color) {
            expect($color)->toMatch('/^#[a-fA-F0-9]{6}$/');
        }
    });

    test('all states have descriptive names', function () {
        $certification = new DivingProfessionalCertification;

        $states = [
            new PendingValidationDivingCertificationState($certification),
            new ActiveDivingCertificationState($certification),
            new ExpiredDivingCertificationState($certification),
            new RevokedDivingCertificationState($certification),
        ];

        foreach ($states as $state) {
            expect($state->name())
                ->toBeString()
                ->and(strlen($state->name()))
                ->toBeGreaterThan(3); // Names should be meaningful
        }
    });
});
