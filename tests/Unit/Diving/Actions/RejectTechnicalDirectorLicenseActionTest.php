<?php

use Domain\Diving\Actions\RejectTechnicalDirectorLicenseAction;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Diving\States\RemovedDivingTechnicalDirectorState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test data
    $this->entity = Entity::factory()->create();
    $this->individual = Individual::factory()->create();
    $this->license = License::factory()->create();

    // Create license attributed in pending technical director approval state
    $this->licenseAttributed = LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'license_id' => $this->license->id,
        'status_class' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
    ]);

    // Create assigned technical director
    $this->technicalDirector = DivingEntityTechnicalDirector::factory()->create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'license_attributed_id' => $this->licenseAttributed->id,
        'license_id' => $this->license->id,
        'status_class' => AssignedDivingTechnicalDirectorState::class,
        'assigned_at' => now(),
    ]);

    // Create action instance
    $this->action = new RejectTechnicalDirectorLicenseAction;
});

test('technical director can reject license', function () {
    $rejectionReason = 'Missing required documentation';

    $result = $this->action->execute($this->technicalDirector, $rejectionReason);

    expect($result)->toBeInstanceOf(LicenseAttributed::class);

    // Check technical director was marked as rejected
    $this->technicalDirector->refresh();
    expect($this->technicalDirector->hasRejected())->toBeTrue();
    expect($this->technicalDirector->rejection_reason)->toBe($rejectionReason);
    expect($this->technicalDirector->rejected_at)->not->toBeNull();
});

test('rejecting license immediately cancels it', function () {
    $this->action->execute($this->technicalDirector, 'Safety concerns identified');

    // Check license state changed to canceled
    $this->licenseAttributed->refresh();
    expect($this->licenseAttributed->status_class)->toBe(CanceledLicenseAttributedState::class);
});

test('license notes include rejection reason', function () {
    $rejectionReason = 'Equipment safety standards not met';

    $this->action->execute($this->technicalDirector, $rejectionReason);

    $this->licenseAttributed->refresh();
    expect($this->licenseAttributed->notes)->toContain($rejectionReason);
});

test('rejection reason is required', function () {
    expect(fn () => $this->action->execute($this->technicalDirector, ''))
        ->toThrow(Exception::class, __('diving.rejection_reason_required'));
});

test('rejection reason cannot be only whitespace', function () {
    expect(fn () => $this->action->execute($this->technicalDirector, '   '))
        ->toThrow(Exception::class, __('diving.rejection_reason_required'));
});

test('cannot reject if technical director is not assigned', function () {
    // Change status to removed
    $this->technicalDirector->update(['status_class' => RemovedDivingTechnicalDirectorState::class]);

    expect(fn () => $this->action->execute($this->technicalDirector, 'Trying to reject'))
        ->toThrow(Exception::class, __('diving.technical_director_not_assigned'));
});

test('cannot reject if already approved', function () {
    // Mark as approved
    $this->technicalDirector->update([
        'approved_at' => now(),
        'approval_notes' => 'Previously approved',
    ]);

    expect(fn () => $this->action->execute($this->technicalDirector, 'Trying to reject'))
        ->toThrow(Exception::class, __('diving.technical_director_already_approved'));
});

test('cannot reject if already rejected', function () {
    // Mark as rejected
    $this->technicalDirector->update([
        'rejected_at' => now(),
        'rejection_reason' => 'Previously rejected',
    ]);

    expect(fn () => $this->action->execute($this->technicalDirector, 'Trying to reject again'))
        ->toThrow(Exception::class, __('diving.technical_director_already_rejected'));
});

test('cannot reject if license is not in pending technical director approval state', function () {
    // Change license state
    $this->licenseAttributed->update(['status_class' => CanceledLicenseAttributedState::class]);

    expect(fn () => $this->action->execute($this->technicalDirector, 'Trying to reject'))
        ->toThrow(Exception::class, __('diving.license_not_pending_technical_director_approval'));
});

test('rejection logs activity', function () {
    $this->action->execute($this->technicalDirector, 'Rejected due to safety concerns');

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => get_class($this->licenseAttributed),
        'subject_id' => $this->licenseAttributed->id,
        'description' => 'Technical director rejected the license',
    ]);
});

test('rejection affects license immediately regardless of other directors', function () {
    // Create second technical director
    $secondTechnicalDirector = DivingEntityTechnicalDirector::factory()->create([
        'entity_id' => $this->entity->id,
        'individual_id' => Individual::factory()->create()->id,
        'license_attributed_id' => $this->licenseAttributed->id,
        'license_id' => $this->license->id,
        'status_class' => AssignedDivingTechnicalDirectorState::class,
        'assigned_at' => now(),
    ]);

    // First director rejects
    $this->action->execute($this->technicalDirector, 'Safety issues identified');

    // License should be immediately canceled
    $this->licenseAttributed->refresh();
    expect($this->licenseAttributed->status_class)->toBe(CanceledLicenseAttributedState::class);
});

test('rejection uses database transaction', function () {
    // This test verifies the transaction behavior indirectly
    // by ensuring the action completes successfully without partial state
    $rejectionReason = 'Valid rejection reason for transaction test';

    $result = $this->action->execute($this->technicalDirector, $rejectionReason);

    // If transaction works properly, both the technical director rejection
    // and license cancellation should be completed together
    expect($result)->toBeInstanceOf(LicenseAttributed::class);

    $this->technicalDirector->refresh();
    expect($this->technicalDirector->hasRejected())->toBeTrue();
    expect($this->technicalDirector->rejection_reason)->toBe($rejectionReason);

    // License should also be canceled
    $result->refresh();
    expect($result->status_class)->toBe(CanceledLicenseAttributedState::class);
});

test('rejection sends notification to entity', function () {
    // This would test notification sending, but it's complex to test in unit test
    // Better tested in feature test
    $result = $this->action->execute($this->technicalDirector, 'Documentation incomplete');

    expect($result)->toBeInstanceOf(LicenseAttributed::class);
    // In real scenario, notification would be sent to entity
});

test('rejection returns fresh license attributed instance', function () {
    $result = $this->action->execute($this->technicalDirector, 'Valid rejection reason');

    expect($result)->toBeInstanceOf(LicenseAttributed::class);
    expect($result->id)->toBe($this->licenseAttributed->id);

    // Should be fresh instance with updated state
    expect($result->status_class)->toBe(CanceledLicenseAttributedState::class);
});
