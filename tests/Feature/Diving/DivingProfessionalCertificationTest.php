<?php

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\ExpiredDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Diving\States\RevokedDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->individual = Individual::factory()->create();
});

describe('DivingProfessionalCertification Model', function () {
    test('can create diving professional certification', function () {
        $certification = DivingProfessionalCertification::create([
            'individual_id' => $this->individual->id,
            'certification_name' => 'Open Water Instructor',
            'certification_system' => 'SSI',
            'certification_level' => 'Instructor',
            'certification_number' => 'SSI-123456',
            'national_equivalency' => 'N2',
            'issue_date' => now()->subYear(),
            'expiration_date' => now()->addYear(),
            'status_class' => PendingValidationDivingCertificationState::class,
        ]);

        expect($certification)
            ->toBeInstanceOf(DivingProfessionalCertification::class)
            ->individual_id->toBe($this->individual->id)
            ->certification_system->toBe('SSI')
            ->state->toBeInstanceOf(PendingValidationDivingCertificationState::class);
    });

    test('certification relationships work correctly', function () {
        $certification = DivingProfessionalCertification::factory()
            ->for($this->individual)
            ->create();

        expect($certification->individual)
            ->toBeInstanceOf(Individual::class)
            ->id->toBe($this->individual->id);
    });

    test('certification state transitions work correctly', function () {
        $certification = DivingProfessionalCertification::factory()->create([
            'status_class' => PendingValidationDivingCertificationState::class,
        ]);

        expect($certification->state)->toBeInstanceOf(PendingValidationDivingCertificationState::class);

        $certification->transitionTo(ActiveDivingCertificationState::class);
        expect($certification->fresh()->state)->toBeInstanceOf(ActiveDivingCertificationState::class);

        $certification->transitionTo(ExpiredDivingCertificationState::class);
        expect($certification->fresh()->state)->toBeInstanceOf(ExpiredDivingCertificationState::class);
    });

    test('cannot transition to invalid state', function () {
        $certification = DivingProfessionalCertification::factory()->create();

        expect(fn () => $certification->transitionTo('InvalidState'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('isActive method works correctly', function () {
        $activeNoExpiration = DivingProfessionalCertification::factory()->create([
            'status_class' => ActiveDivingCertificationState::class,
            'expiration_date' => null,
        ]);

        $activeFutureExpiration = DivingProfessionalCertification::factory()->create([
            'status_class' => ActiveDivingCertificationState::class,
            'expiration_date' => now()->addYear(),
        ]);

        $activePastExpiration = DivingProfessionalCertification::factory()->create([
            'status_class' => ActiveDivingCertificationState::class,
            'expiration_date' => now()->subDay(),
        ]);

        $pending = DivingProfessionalCertification::factory()->create([
            'status_class' => PendingValidationDivingCertificationState::class,
        ]);

        expect($activeNoExpiration->isActive())->toBeTrue()
            ->and($activeFutureExpiration->isActive())->toBeTrue()
            ->and($activePastExpiration->isActive())->toBeFalse()
            ->and($pending->isActive())->toBeFalse();
    });

    test('isExpired method works correctly', function () {
        $noExpiration = DivingProfessionalCertification::factory()->create(['expiration_date' => null]);
        $futureExpiration = DivingProfessionalCertification::factory()->create(['expiration_date' => now()->addDay()]);
        $pastExpiration = DivingProfessionalCertification::factory()->create(['expiration_date' => now()->subDay()]);

        expect($noExpiration->isExpired())->toBeFalse()
            ->and($futureExpiration->isExpired())->toBeFalse()
            ->and($pastExpiration->isExpired())->toBeTrue();
    });

    test('media collection works correctly', function () {
        Storage::fake('public');
        $certification = DivingProfessionalCertification::factory()->create();

        $media = $certification->addMedia(UploadedFile::fake()->image('certificate.jpg', 100, 100))
            ->toMediaCollection('certificate_documents');

        expect($certification->getFirstMedia('certificate_documents'))->not->toBeNull()
            ->and($media->mime_type)->toBe('image/jpeg');
    });

    test('scopes work correctly', function () {
        $activeCert = DivingProfessionalCertification::factory()->create([
            'status_class' => ActiveDivingCertificationState::class,
            'expiration_date' => now()->addYear(),
            'certification_system' => 'SSI',
        ]);

        $pendingCert = DivingProfessionalCertification::factory()->create([
            'status_class' => PendingValidationDivingCertificationState::class,
            'certification_system' => 'PADI',
        ]);

        $expiredCert = DivingProfessionalCertification::factory()->create([
            'status_class' => ActiveDivingCertificationState::class,
            'expiration_date' => now()->subDay(),
            'certification_system' => 'SSI',
        ]);

        expect(DivingProfessionalCertification::active()->pluck('id'))
            ->toContain($activeCert->id);

        expect(DivingProfessionalCertification::pendingValidation()->pluck('id'))
            ->toContain($pendingCert->id);

        expect(DivingProfessionalCertification::forSystem('SSI')->pluck('id'))
            ->toContain($activeCert->id)
            ->toContain($expiredCert->id);

        expect(DivingProfessionalCertification::forSystem('PADI')->pluck('id'))
            ->toContain($pendingCert->id);
    });
});

describe('DivingProfessionalCertification States', function () {
    test('pending validation state methods work correctly', function () {
        $certification = DivingProfessionalCertification::factory()
            ->create(['status_class' => PendingValidationDivingCertificationState::class]);

        expect($certification->canBeValidated())->toBeTrue()
            ->and($certification->canBeRevoked())->toBeFalse();
    });

    test('active state methods work correctly', function () {
        $certification = DivingProfessionalCertification::factory()
            ->create(['status_class' => ActiveDivingCertificationState::class]);

        expect($certification->canBeValidated())->toBeFalse()
            ->and($certification->canBeRevoked())->toBeTrue();
    });

    test('expired state methods work correctly', function () {
        $certification = DivingProfessionalCertification::factory()
            ->create(['status_class' => ExpiredDivingCertificationState::class]);

        expect($certification->canBeValidated())->toBeFalse()
            ->and($certification->canBeRevoked())->toBeFalse();
    });

    test('revoked state methods work correctly', function () {
        $certification = DivingProfessionalCertification::factory()
            ->create(['status_class' => RevokedDivingCertificationState::class]);

        expect($certification->canBeValidated())->toBeFalse()
            ->and($certification->canBeRevoked())->toBeFalse();
    });
});
