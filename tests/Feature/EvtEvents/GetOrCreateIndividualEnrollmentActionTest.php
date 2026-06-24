<?php

use App\Enums\EvtEventFeeTypeEnum;
use App\Models\User;
use Domain\EvtEvents\Actions\GetOrCreateIndividualEnrollmentAction;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::partialMock();

    // Setup common models and assign to $this
    $this->user = User::factory()->create();
    $this->individual = Individual::factory()->create();
    $this->event = Event::factory()->create();

    // Login the user if needed for Auth::id() context (optional, depending on test needs)
    // $this->actingAs($this->user);
});

// Helper function to create event pricing
function createEventPricingForTest(Event $event, EvtEventFeeTypeEnum $priceType): Pricing
{
    return Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => $priceType->value,
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        // Add other necessary fields like target_group, enrollment_role if required by the query
    ]);
}

test('creates new reusable enrollment when PER_PERSON pricing exists and no prior enrollment', function () {
    // Arrange
    createEventPricingForTest($this->event, EvtEventFeeTypeEnum::PER_PERSON);
    $cacheKey = "evt:{$this->event->id}:has_single_charge_pricing";
    Cache::shouldReceive('remember')->once()->with($cacheKey, Mockery::any(), Mockery::any())->andReturn(true);

    $action = app(GetOrCreateIndividualEnrollmentAction::class);

    // Act
    $enrollment = $action->execute($this->event, $this->individual, $this->user->id);

    // Assert
    expect($enrollment)->toBeInstanceOf(Enrollment::class);
    expect($enrollment->wasRecentlyCreated)->toBeTrue();
    $this->assertDatabaseHas('evt_enrollments', [
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
    ]);
    $this->assertDatabaseCount('evt_enrollments', 1);
});

test('reuses existing enrollment when PER_PERSON pricing exists and enrollment exists', function () {
    // Arrange
    createEventPricingForTest($this->event, EvtEventFeeTypeEnum::PER_PERSON);
    $existingEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
    ]);
    $this->assertDatabaseCount('evt_enrollments', 1);

    $cacheKey = "evt:{$this->event->id}:has_single_charge_pricing";
    Cache::shouldReceive('remember')->once()->with($cacheKey, Mockery::any(), Mockery::any())->andReturn(true);

    $action = app(GetOrCreateIndividualEnrollmentAction::class);

    // Act
    $enrollment = $action->execute($this->event, $this->individual, $this->user->id);

    // Assert
    expect($enrollment->id)->toBe($existingEnrollment->id);
    expect($enrollment->wasRecentlyCreated)->toBeFalse();
    $this->assertDatabaseCount('evt_enrollments', 1);
});

test('creates new per-discipline enrollment when no reusable pricing exists and no prior enrollment', function () {
    // Arrange
    // No PER_PERSON/EVENT_FEE pricing created
    $cacheKey = "evt:{$this->event->id}:has_single_charge_pricing";
    Cache::shouldReceive('remember')->once()->with($cacheKey, Mockery::any(), Mockery::any())->andReturn(false);

    $action = app(GetOrCreateIndividualEnrollmentAction::class);

    // Act
    $enrollment = $action->execute($this->event, $this->individual, $this->user->id);

    // Assert
    expect($enrollment->wasRecentlyCreated)->toBeTrue();
    $this->assertDatabaseHas('evt_enrollments', [
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'user_id' => $this->user->id,
    ]);
    $this->assertDatabaseCount('evt_enrollments', 1);
});

test('creates new per-discipline enrollment even when prior enrollment exists if no reusable pricing exists', function () {
    // Arrange
    $existingEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
    ]);
    $this->assertDatabaseCount('evt_enrollments', 1);

    $cacheKey = "evt:{$this->event->id}:has_single_charge_pricing";
    Cache::shouldReceive('remember')->once()->with($cacheKey, Mockery::any(), Mockery::any())->andReturn(false);

    $action = app(GetOrCreateIndividualEnrollmentAction::class);

    // Act
    $newEnrollment = $action->execute($this->event, $this->individual, $this->user->id);

    // Assert
    expect($newEnrollment->wasRecentlyCreated)->toBeTrue();
    expect($newEnrollment->id)->not->toBe($existingEnrollment->id);
    $this->assertDatabaseCount('evt_enrollments', 2);
});
