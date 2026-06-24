<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\User;
use Domain\EvtEvents\Actions\RemovePendingAthleteEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // --- Test Data ---
    $this->actingUser = User::factory()->create();
    $this->individual = Individual::factory()->create();
    $this->discipline = Discipline::factory()->create();

    $this->parentEnrollment = Enrollment::factory()->create([
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
    ]);

    // Create the enrollment to be removed
    $this->enrollmentToRemove = AthleteEnrollment::factory()->create([
        'individual_id' => $this->individual->id,
        'enrollment_id' => $this->parentEnrollment->id,
        'discipline_id' => $this->discipline->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value, // Must be REGISTERED
        'federation_id' => null,
    ]);

    // --- Action Instance ---
    $this->action = app(RemovePendingAthleteEnrollmentAction::class);
});

it('successfully removes a pending enrollment', function () {
    // Arrange
    $enrollmentId = $this->enrollmentToRemove->id;

    // Act
    $result = $this->action->execute($enrollmentId, (string) $this->individual->id, $this->actingUser);

    // Assert
    expect($result['success'])->toBeTrue()
        ->and($result['message'])->toContain('successfully removed');

    // Assert database state
    $this->assertDatabaseMissing('evt_athletes_enrollment', [
        'id' => $enrollmentId,
    ]);
    // Optionally check for related attribute deletion if applicable
});

it('returns error if enrollment not found', function () {
    // Act
    $result = $this->action->execute(99999, (string) $this->individual->id, $this->actingUser);

    // Assert
    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Enrollment not found');
});

it('returns error if individual ID does not match', function () {
    // Arrange
    $enrollmentId = $this->enrollmentToRemove->id;
    $wrongIndividualId = Individual::factory()->create()->id; // Different individual

    // Act
    $result = $this->action->execute($enrollmentId, (string) $wrongIndividualId, $this->actingUser);

    // Assert
    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Cannot remove this enrollment');

    // Assert enrollment still exists
    $this->assertDatabaseHas('evt_athletes_enrollment', ['id' => $enrollmentId]);
});

it('returns error if enrollment status is not REGISTERED', function () {
    // Arrange
    $enrollmentId = $this->enrollmentToRemove->id;
    // Update status to something else
    $this->enrollmentToRemove->update(['status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value]);

    // Act
    $result = $this->action->execute($enrollmentId, (string) $this->individual->id, $this->actingUser);

    // Assert
    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Cannot remove this enrollment');

    // Assert enrollment still exists
    $this->assertDatabaseHas('evt_athletes_enrollment', ['id' => $enrollmentId]);
});
