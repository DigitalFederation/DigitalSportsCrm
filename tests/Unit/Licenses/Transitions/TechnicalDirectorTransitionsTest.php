<?php

use App\Models\User;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Licenses\Transitions\TechnicalDirectorApprovalToCanceledTransition;
use Domain\Licenses\Transitions\TechnicalDirectorApprovalToPendingValidationTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);

    $this->entity = Entity::factory()->create();
    $this->license = License::factory()->create();

    $this->licenseAttributed = LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'license_id' => $this->license->id,
        'status_class' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
    ]);

    $this->createTechnicalDirector = function (bool $approved = true): DivingEntityTechnicalDirector {
        return DivingEntityTechnicalDirector::factory()->create([
            'entity_id' => $this->entity->id,
            'individual_id' => Individual::factory()->create()->id,
            'license_attributed_id' => $this->licenseAttributed->id,
            'license_id' => $this->license->id,
            'status_class' => AssignedDivingTechnicalDirectorState::class,
            'approved_at' => $approved ? now() : null,
        ]);
    };
});

describe('TechnicalDirectorApprovalToPendingValidationTransition', function () {
    test('transitions license to pending validation when all directors approved', function () {
        ($this->createTechnicalDirector)(approved: true);
        ($this->createTechnicalDirector)(approved: true);

        $transition = new TechnicalDirectorApprovalToPendingValidationTransition($this->licenseAttributed);
        $result = $transition->handle();

        expect($result)
            ->toBeInstanceOf(LicenseAttributed::class)
            ->status_class->toBe(PendingValidationLicenseAttributedState::class);

        $this->assertDatabaseHas('license_attributed', [
            'id' => $this->licenseAttributed->id,
            'status_class' => PendingValidationLicenseAttributedState::class,
        ]);
    });

    test('throws exception when license is not in pending TD approval state', function () {
        $this->licenseAttributed->update(['status_class' => PendingValidationLicenseAttributedState::class]);

        $transition = new TechnicalDirectorApprovalToPendingValidationTransition($this->licenseAttributed);

        expect(fn () => $transition->handle())
            ->toThrow(Exception::class, 'License must be in pending technical director approval state');
    });

    test('throws exception when not all directors have approved', function () {
        ($this->createTechnicalDirector)(approved: true);
        ($this->createTechnicalDirector)(approved: false);

        $transition = new TechnicalDirectorApprovalToPendingValidationTransition($this->licenseAttributed);

        expect(fn () => $transition->handle())
            ->toThrow(Exception::class, 'All technical directors must approve before transitioning');
    });

    test('logs activity on successful transition', function () {
        ($this->createTechnicalDirector)(approved: true);

        $transition = new TechnicalDirectorApprovalToPendingValidationTransition($this->licenseAttributed);
        $transition->handle();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => get_class($this->licenseAttributed),
            'subject_id' => $this->licenseAttributed->id,
            'description' => 'All technical directors approved - license pending admin validation',
        ]);
    });
});

describe('TechnicalDirectorApprovalToCanceledTransition', function () {
    test('transitions license to canceled state with rejection reason', function () {
        $rejectionReason = 'Safety standards not met';

        $transition = new TechnicalDirectorApprovalToCanceledTransition($this->licenseAttributed, $rejectionReason);
        $result = $transition->handle();

        expect($result)
            ->toBeInstanceOf(LicenseAttributed::class)
            ->status_class->toBe(CanceledLicenseAttributedState::class)
            ->notes->toBe('Technical director rejection: '.$rejectionReason);

        $this->assertDatabaseHas('license_attributed', [
            'id' => $this->licenseAttributed->id,
            'status_class' => CanceledLicenseAttributedState::class,
            'notes' => 'Technical director rejection: '.$rejectionReason,
        ]);
    });

    test('throws exception when license is not in pending TD approval state', function () {
        $this->licenseAttributed->update(['status_class' => CanceledLicenseAttributedState::class]);

        $transition = new TechnicalDirectorApprovalToCanceledTransition($this->licenseAttributed, 'Test reason');

        expect(fn () => $transition->handle())
            ->toThrow(Exception::class, 'License must be in pending technical director approval state');
    });

    test('logs activity with rejection reason', function () {
        $rejectionReason = 'Documentation incomplete';

        $transition = new TechnicalDirectorApprovalToCanceledTransition($this->licenseAttributed, $rejectionReason);
        $transition->handle();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => get_class($this->licenseAttributed),
            'subject_id' => $this->licenseAttributed->id,
            'description' => 'License canceled due to technical director rejection',
        ]);

        $activity = \Spatie\Activitylog\Models\Activity::where([
            'subject_type' => get_class($this->licenseAttributed),
            'subject_id' => $this->licenseAttributed->id,
        ])->first();

        expect($activity->properties->get('rejection_reason'))->toBe($rejectionReason);
    });

    test('accepts empty rejection reason in constructor', function () {
        $transition = new TechnicalDirectorApprovalToCanceledTransition($this->licenseAttributed, '');

        expect($transition)->toBeInstanceOf(TechnicalDirectorApprovalToCanceledTransition::class);
    });
});
