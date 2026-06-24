<?php

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ExpiredAffiliationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- Helper to wire up Entity with a valid validation plan affiliation ---
function createEntityWithValidationPlanAffiliation(Entity $entity, Federation $federation, array $affiliationOverrides = []): Affiliation
{
    $package = MembershipPackage::factory()->entity()->create();
    $plan = AffiliationPlan::factory()->validation()->create(['federation_id' => $federation->id]);
    $package->affiliationPlans()->attach($plan);

    $subscription = MemberSubscription::factory()->forEntity($entity)->create([
        'membership_package_id' => $package->id,
    ]);

    return Affiliation::factory()->forEntity($entity)->create(array_merge([
        'federation_id' => $federation->id,
        'member_subscription_id' => $subscription->id,
        'status_class' => ActiveAffiliationState::class,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
    ], $affiliationOverrides));
}

// --- Helper to wire up Individual with a valid validation plan affiliation ---
function createIndividualWithValidationPlanAffiliation(Individual $individual, Federation $federation, array $affiliationOverrides = []): Affiliation
{
    $package = MembershipPackage::factory()->individual()->create();
    $plan = AffiliationPlan::factory()->validation()->create(['federation_id' => $federation->id]);
    $package->affiliationPlans()->attach($plan);

    $subscription = MemberSubscription::factory()->forIndividual($individual)->create([
        'membership_package_id' => $package->id,
    ]);

    return Affiliation::factory()->forIndividual($individual)->create(array_merge([
        'federation_id' => $federation->id,
        'member_subscription_id' => $subscription->id,
        'status_class' => ActiveAffiliationState::class,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
    ], $affiliationOverrides));
}

// ============================
// Entity - hasActiveValidationPlanAffiliation
// ============================

test('entity has active validation plan affiliation returns true', function () {
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $entity = Entity::factory()->create();

    createEntityWithValidationPlanAffiliation($entity, $federation);

    expect($entity->hasActiveValidationPlanAffiliation())->toBeTrue();
});

test('entity with expired affiliation returns false', function () {
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $entity = Entity::factory()->create();

    createEntityWithValidationPlanAffiliation($entity, $federation, [
        'status_class' => ExpiredAffiliationState::class,
        'start_date' => now()->subYears(2),
        'end_date' => now()->subMonth(),
    ]);

    expect($entity->hasActiveValidationPlanAffiliation())->toBeFalse();
});

test('entity with non-validation plan returns false', function () {
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $entity = Entity::factory()->create();

    $package = MembershipPackage::factory()->entity()->create();
    // Non-validation plan (is_validation_plan = false)
    $plan = AffiliationPlan::factory()->create(['federation_id' => $federation->id, 'is_validation_plan' => false]);
    $package->affiliationPlans()->attach($plan);

    $subscription = MemberSubscription::factory()->forEntity($entity)->create([
        'membership_package_id' => $package->id,
    ]);

    Affiliation::factory()->forEntity($entity)->create([
        'federation_id' => $federation->id,
        'member_subscription_id' => $subscription->id,
        'status_class' => ActiveAffiliationState::class,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
    ]);

    expect($entity->hasActiveValidationPlanAffiliation())->toBeFalse();
});

test('entity with non-default federation returns false', function () {
    $federation = Federation::factory()->create(['is_default_federation' => false]);
    $entity = Entity::factory()->create();

    createEntityWithValidationPlanAffiliation($entity, $federation);

    expect($entity->hasActiveValidationPlanAffiliation())->toBeFalse();
});

// ============================
// Entity scope - filterAffiliationStatus
// ============================

test('entity scope filters active affiliation status correctly', function () {
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $activeEntity = Entity::factory()->create();
    $inactiveEntity = Entity::factory()->create();

    createEntityWithValidationPlanAffiliation($activeEntity, $federation);

    $activeResults = Entity::filterAffiliationStatus('active')->pluck('id');
    $inactiveResults = Entity::filterAffiliationStatus('inactive')->pluck('id');

    expect($activeResults)->toContain($activeEntity->id)
        ->and($activeResults)->not->toContain($inactiveEntity->id)
        ->and($inactiveResults)->toContain($inactiveEntity->id)
        ->and($inactiveResults)->not->toContain($activeEntity->id);
});

// ============================
// Individual - hasActiveValidationPlanAffiliation
// ============================

test('individual has active validation plan affiliation returns true', function () {
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $individual = Individual::factory()->create();

    createIndividualWithValidationPlanAffiliation($individual, $federation);

    expect($individual->hasActiveValidationPlanAffiliation())->toBeTrue();
});

test('individual with no affiliation returns false', function () {
    $individual = Individual::factory()->create();

    expect($individual->hasActiveValidationPlanAffiliation())->toBeFalse();
});

// ============================
// Individual scope - filterNationalAffiliationStatus
// ============================

test('individual scope filters active affiliation status correctly', function () {
    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $activeIndividual = Individual::factory()->create();
    $inactiveIndividual = Individual::factory()->create();

    createIndividualWithValidationPlanAffiliation($activeIndividual, $federation);

    $activeResults = Individual::filterNationalAffiliationStatus('active')->pluck('id');
    $inactiveResults = Individual::filterNationalAffiliationStatus('inactive')->pluck('id');

    expect($activeResults)->toContain($activeIndividual->id)
        ->and($activeResults)->not->toContain($inactiveIndividual->id)
        ->and($inactiveResults)->toContain($inactiveIndividual->id)
        ->and($inactiveResults)->not->toContain($activeIndividual->id);
});
