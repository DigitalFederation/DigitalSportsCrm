<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\CheckExistingEventEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * This test suite tests the CheckExistingEventEnrollmentAction that validates
 * the cross-organization athlete registration scenarios.
 */
beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();

    // Create event with competition
    $this->event = Event::factory()->create([
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
    ]);

    // Create competition with discipline limit
    $this->competition = Competition::factory()->create([
        'event_id' => $this->event->id,
        'max_disciplines_per_athlete' => 3,
    ]);

    // Create two distinct disciplines
    $this->discipline1 = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'name' => 'Discipline 1',
    ]);

    $this->discipline2 = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'name' => 'Discipline 2',
    ]);

    // Create federation and entity
    $this->federation = Federation::factory()->create(['name' => 'Test Federation']);
    $this->entity = Entity::factory()->create(['name' => 'Test Club']);

    // Link entity to federation
    $this->entity->federations()->attach($this->federation->id);

    // Create athlete
    $this->athlete = Individual::factory()->create([
        'name' => 'Test Athlete',
        'surname' => 'Cross Registration',
    ]);

    // Create individual-federation and individual-entity relationships
    IndividualFederation::create([
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    IndividualEntity::create([
        'individual_id' => $this->athlete->id,
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Create action to test
    $this->action = new CheckExistingEventEnrollmentAction;
});

it('allows federation to register athlete not yet registered by anyone', function () {
    // Test 1: Federation can register athlete initially
    $result = $this->action->execute($this->event, $this->athlete, '', $this->federation);
    expect($result['can_register'])->toBeTrue('Federation should be able to register athlete');
});

it('allows entity to register athlete not yet registered by anyone', function () {
    $result = $this->action->execute($this->event, $this->athlete, '', $this->entity);
    expect($result['can_register'])->toBeTrue('Entity should be able to register athlete');
});

it('blocks same federation from registering athlete twice', function () {
    // Create federation enrollment
    $federationEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
        'user_id' => $this->user->id,
    ]);

    // First registration
    AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $federationEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'entity_id' => null,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Test 2: Federation cannot register the same athlete again
    $result = $this->action->execute($this->event, $this->athlete, '', $this->federation);
    expect($result['can_register'])->toBeFalse('Federation should not be able to register the same athlete twice');
});

it('blocks same entity from registering athlete twice', function () {
    // Create entity enrollment
    $entityEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'user_id' => $this->user->id,
    ]);

    AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $entityEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => null,
        'entity_id' => $this->entity->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Entity cannot register the same athlete again
    $result = $this->action->execute($this->event, $this->athlete, '', $this->entity);
    expect($result['can_register'])->toBeFalse('Entity should not be able to register the same athlete twice');
});

it('blocks registration by second federation when first federation already registered', function () {
    // Create federation enrollment
    $federationEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
        'user_id' => $this->user->id,
    ]);

    AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $federationEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'entity_id' => null,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Create a second federation
    $federation2 = Federation::factory()->create(['name' => 'Second Federation']);

    // Individual is registered only by one federation, other federations should be blocked
    $result = $this->action->execute($this->event, $this->athlete, '', $federation2);
    expect($result['can_register'])->toBeFalse('Second federation should not be able to register athlete');
});

it('allows cross-organization registration', function () {
    // Create federation enrollment
    $federationEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
        'user_id' => $this->user->id,
    ]);

    AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $federationEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'entity_id' => null,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Entity can register the same athlete (cross-organization registration)
    // Note: This test reflects the initial registration with empty discipline ID
    // which is how the EventRegistration component calls the action
    $result = $this->action->execute($this->event, $this->athlete, '', $this->entity);
    expect($result['can_register'])->toBeTrue('Entity should be able to register athlete already registered by federation');
});

/**
 * This test reflects the actual two-step workflow:
 * 1. First, both organizations register the athlete (with no discipline)
 * 2. Then, in the ManageEnrollment component, they assign disciplines
 */
it('handles discipline assignments correctly between organizations', function () {
    // 1. Federation registers the athlete (initial registration, no discipline)
    $federationEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
        'user_id' => $this->user->id,
    ]);

    // Federation athlete enrollment (with discipline assigned in the ManageEnrollment step)
    $federationAthleteEnrollment = AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $federationEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'entity_id' => null,
        'discipline_id' => $this->discipline1->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // 2. Entity registers the athlete (initial registration, no discipline)
    $entityEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'user_id' => $this->user->id,
    ]);

    // Entity athlete enrollment (without discipline initially)
    $entityAthleteEnrollment = AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $entityEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => null,
        'entity_id' => $this->entity->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // 3. In ManageEnrollment component, we would now check if disciplines can be assigned

    // Check if Entity can assign the same discipline as Federation - should NOT be allowed
    // This would be done in ManageEnrollment, not EventRegistration
    $result = $this->action->execute($this->event, $this->athlete, $this->discipline1->id, $this->entity);
    expect($result['can_register'])->toBeFalse('Entity should not be able to assign same discipline as federation');

    // Check if Entity can assign a different discipline - should be allowed
    $result = $this->action->execute($this->event, $this->athlete, $this->discipline2->id, $this->entity);
    expect($result['can_register'])->toBeTrue('Entity should be able to assign different discipline to athlete');
});

/**
 * This test verifies that when an athlete self-registers for an event,
 * organizations (federations/entities) cannot register them.
 */
it('blocks organization registration when athlete has self-registered', function () {
    // Create a self-registration enrollment (no federation_id/entity_id)
    $selfEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->athlete->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
    ]);

    // Create self athlete enrollment
    AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $selfEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => null,
        'entity_id' => null,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Federation should not be able to register an athlete who has self-registered
    $federationResult = $this->action->execute($this->event, $this->athlete, '', $this->federation);
    expect($federationResult['can_register'])->toBeFalse('Federation should not be able to register a self-registered athlete');

    // Entity should not be able to register an athlete who has self-registered
    $entityResult = $this->action->execute($this->event, $this->athlete, '', $this->entity);
    expect($entityResult['can_register'])->toBeFalse('Entity should not be able to register a self-registered athlete');

    // Check for appropriate error messages
    expect($federationResult['message'])->toContain('self-registered');
    expect($entityResult['message'])->toContain('self-registered');
});

/**
 * This test verifies that registrations with different payment statuses
 * still enforce the same validation rules for registration
 */
it('handles different payment status scenarios correctly', function () {
    // Create federation enrollment with pending payment status
    $federationEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
        'user_id' => $this->user->id,
    ]);

    // Create athlete enrollment with pending payment status
    AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $federationEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'entity_id' => null,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
        'per_person_price' => 50,
        'discipline_price' => 25,
        'event_fee' => 10,
        'total_price' => 85,
    ]);

    // The same federation should not be able to register again
    $result = $this->action->execute($this->event, $this->athlete, '', $this->federation);
    expect($result['can_register'])->toBeFalse('Federation should not be able to register the same athlete twice');

    // Another federation should still be blocked regardless of payment status
    $federation2 = Federation::factory()->create(['name' => 'Second Federation']);
    $result2 = $this->action->execute($this->event, $this->athlete, '', $federation2);
    expect($result2['can_register'])->toBeFalse('Second federation should not be able to register athlete');

    // But entity should still be able to register the athlete (cross-organization)
    $entityResult = $this->action->execute($this->event, $this->athlete, '', $this->entity);
    expect($entityResult['can_register'])->toBeTrue('Entity should be able to register athlete with pending federation payment');
});

/**
 * This test verifies that removing enrollments doesn't affect
 * cross-organization registration capabilities
 */
it('maintains registration integrity when enrollments are removed', function () {
    // Create federation enrollment
    $federationEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
        'user_id' => $this->user->id,
    ]);

    // Create athlete enrollment for federation
    $federationAthleteEnrollment = AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $federationEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => $this->federation->id,
        'entity_id' => null,
        'discipline_id' => $this->discipline1->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Create entity enrollment
    $entityEnrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'user_id' => $this->user->id,
    ]);

    // Create athlete enrollment for entity
    $entityAthleteEnrollment = AthleteEnrollment::create([
        'event_id' => $this->event->id,
        'enrollment_id' => $entityEnrollment->id,
        'individual_id' => $this->athlete->id,
        'federation_id' => null,
        'entity_id' => $this->entity->id,
        'discipline_id' => $this->discipline2->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
    ]);

    // Simulate removal of federation enrollment
    $federationAthleteEnrollment->delete();

    // Entity should still maintain its enrollment
    $entityCheck = AthleteEnrollment::where([
        'event_id' => $this->event->id,
        'individual_id' => $this->athlete->id,
        'entity_id' => $this->entity->id,
    ])->exists();

    expect($entityCheck)->toBeTrue('Entity enrollment should still exist after federation enrollment is removed');

    // Federation should be able to register again
    $reRegisterResult = $this->action->execute($this->event, $this->athlete, $this->discipline1->id, $this->federation);
    expect($reRegisterResult['can_register'])->toBeTrue('Federation should be able to register again after enrollment removed');

    // Entity should still be blocked from registering the athlete for the same discipline
    $disciplineConflictResult = $this->action->execute($this->event, $this->athlete, $this->discipline2->id, $this->federation);
    expect($disciplineConflictResult['can_register'])->toBeFalse('Federation should not be able to register for discipline already assigned to entity');
});
