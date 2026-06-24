<?php

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Licenses\Transitions\PendingValidationToActiveTransition;
use Domain\Licenses\Transitions\PendingValidationToCanceledTransition;
use Domain\Licenses\Transitions\PendingValidationToPendingTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\CommitteeSeeder::class);
    $this->seed(\Database\Seeders\DivingEntityLicenseSeeder::class);
});

test('pending validation state can transition to pending', function () {
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'total_value' => 250.00,
    ]);

    $state = new PendingValidationLicenseAttributedState($license);

    expect($state->canTransitionTo())->toContain(PendingLicenseAttributedState::class);
    expect($state->canTransitionTo())->toContain(ActiveLicenseAttributedState::class);
    expect($state->canTransitionTo())->toContain(CanceledLicenseAttributedState::class);
});

test('pending validation to pending transition dispatches event for payment', function () {
    Event::fake([LicenseAttributedCreatedEvent::class]);

    $user = \App\Models\User::factory()->create();
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'total_value' => 250.00,
        'validated_by' => $user->id,
        'validated_at' => now(),
        'validation_notes' => 'Approved for payment',
    ]);

    $transition = new PendingValidationToPendingTransition($license);
    $transition->handle();

    // Verify state changed
    expect($license->fresh()->status_class)->toBe(PendingLicenseAttributedState::class);

    // Verify event was dispatched for document creation
    Event::assertDispatched(LicenseAttributedCreatedEvent::class, function ($event) use ($license) {
        return is_array($event->licenseAttributed)
            && count($event->licenseAttributed) === 1
            && $event->licenseAttributed[0]->id === $license->id
            && $event->isSelfRequest === true; // This is from admin approval
    });
});

test('pending validation to active transition for free licenses', function () {
    $user = \App\Models\User::factory()->create();
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'total_value' => 0, // Free license
        'validated_by' => $user->id,
        'validated_at' => now(),
        'validation_notes' => 'Approved - no payment required',
    ]);

    $transition = new PendingValidationToActiveTransition($license);
    $transition->handle();

    // Verify state changed directly to active
    expect($license->fresh()->status_class)->toBe(ActiveLicenseAttributedState::class);
});

test('pending validation to canceled transition', function () {
    $user = \App\Models\User::factory()->create();
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'validated_by' => $user->id,
        'validated_at' => now(),
        'validation_notes' => 'Rejected - missing documents',
    ]);

    $transition = new PendingValidationToCanceledTransition($license);
    $transition->handle();

    // Verify state changed to canceled
    expect($license->fresh()->status_class)->toBe(CanceledLicenseAttributedState::class);
});

test('transition logs activity', function () {
    $user = \App\Models\User::factory()->create();
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'validated_by' => $user->id,
        'validated_at' => now(),
    ]);

    $transition = new PendingValidationToPendingTransition($license);
    $transition->handle();

    // Check activity log
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => get_class($license),
        'subject_id' => $license->id,
        'description' => 'License approved and pending payment',
    ]);
});

test('cannot transition from non-pending-validation state', function () {
    $license = LicenseAttributed::factory()->create([
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $transition = new PendingValidationToPendingTransition($license);

    expect(fn () => $transition->handle())->toThrow(\Exception::class);
});

test('validation fields are preserved during transition', function () {
    $user = \App\Models\User::factory()->create();
    $validatedBy = $user->id;
    $validatedAt = now();
    $validationNotes = 'Test validation notes';

    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'validated_by' => $validatedBy,
        'validated_at' => $validatedAt,
        'validation_notes' => $validationNotes,
    ]);

    $transition = new PendingValidationToPendingTransition($license);
    $transition->handle();

    $license->refresh();
    expect($license->validated_by)->toBe($validatedBy);
    expect($license->validated_at->toDateTimeString())->toBe($validatedAt->toDateTimeString());
    expect($license->validation_notes)->toBe($validationNotes);
});
