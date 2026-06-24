<?php

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\PendingPaymentInsuranceState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Domain\Memberships\States\ActiveAffiliationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(SubscriptionValidationService::class);
});

test('validates affiliation package for individual without existing affiliation', function () {
    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    $affiliationPlan = AffiliationPlan::factory()->create([
        'is_validation_plan' => false,
    ]);
    $package->affiliationPlans()->attach($affiliationPlan);

    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeTrue();
    expect($result['error'])->toBeNull();
});

test('validates insurance-only package requires validation affiliation', function () {
    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    $insurancePlan = InsurancePlan::factory()->create();
    $package->insurancePlans()->attach($insurancePlan);

    // No affiliation plans, so this is insurance-only
    $package->load(['affiliationPlans', 'insurancePlans']);

    // Create an entity user to simulate entity context (not individual self-subscription)
    $entity = Entity::factory()->create();
    $entityUser = \App\Models\User::factory()->create([
        'group_id' => \App\Enums\UserGroupEnum::ENTITY->value, // Set user as entity type
    ]);
    $entity->users()->attach($entityUser);

    // Act as the entity user to simulate entity subscribing an individual
    $this->actingAs($entityUser);

    // Mock ValidationPlanPrivilegeService to return false
    $mockValidationService = $this->mock(ValidationPlanPrivilegeService::class);
    $mockValidationService->shouldReceive('canRequestInsurance')
        ->with($individual)
        ->andReturn(false);
    $mockValidationService->shouldReceive('getValidationPlanReason')
        ->with($individual, 'insurance')
        ->andReturn('No validation affiliation found');

    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain('No validation affiliation found');
});

test('prevents duplicate affiliation plan subscription', function () {
    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    $affiliationPlan = AffiliationPlan::factory()->create();
    $package->affiliationPlans()->attach($affiliationPlan);

    // Create existing subscription with the same affiliation plan
    $existingPackage = MembershipPackage::factory()->create();
    $existingPackage->affiliationPlans()->attach($affiliationPlan);

    $existingSubscription = MemberSubscription::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'membership_package_id' => $existingPackage->id,
    ]);

    Affiliation::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'member_subscription_id' => $existingSubscription->id,
        'federation_id' => $affiliationPlan->federation_id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveAffiliationState::class,
    ]);

    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain($affiliationPlan->name);
});

test('prevents duplicate insurance plan subscription', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Add affiliation plan to make it valid
    $affiliationPlan = AffiliationPlan::factory()->create();
    $package->affiliationPlans()->attach($affiliationPlan);

    $insurancePlan = InsurancePlan::factory()->create();
    $package->insurancePlans()->attach($insurancePlan);

    // Create existing insurance for this plan
    Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan->id,
        'member_type' => 'entity',
        'member_id' => $entity->id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveInsuranceState::class,
    ]);

    $result = $this->service->validateSubscription($entity, $package);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain($insurancePlan->name);
});

test('allows subscription when no duplicates exist', function () {
    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    $affiliationPlan = AffiliationPlan::factory()->create();
    $insurancePlan = InsurancePlan::factory()->create();

    $package->affiliationPlans()->attach($affiliationPlan);
    $package->insurancePlans()->attach($insurancePlan);

    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeTrue();
    expect($result['error'])->toBeNull();
});

test('checks for pending payment insurance as duplicate', function () {
    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    $affiliationPlan = AffiliationPlan::factory()->create();
    $package->affiliationPlans()->attach($affiliationPlan);

    $insurancePlan = InsurancePlan::factory()->create();
    $package->insurancePlans()->attach($insurancePlan);

    // Create pending payment insurance
    Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan->id,
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'status_class' => PendingPaymentInsuranceState::class,
    ]);

    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain($insurancePlan->name);
});

test('validates insurance-only package with active validation affiliation', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    $insurancePlan = InsurancePlan::factory()->create();
    $package->insurancePlans()->attach($insurancePlan);

    // No affiliation plans, so this is insurance-only
    $package->load(['affiliationPlans', 'insurancePlans']);

    // Mock ValidationPlanPrivilegeService to return true
    $mockValidationService = $this->mock(ValidationPlanPrivilegeService::class);
    $mockValidationService->shouldReceive('canRequestInsurance')
        ->with($entity)
        ->andReturn(true);

    $result = $this->service->validateSubscription($entity, $package);

    expect($result['valid'])->toBeTrue();
    expect($result['error'])->toBeNull();
});

test('entity cannot assign non-validation package to individual without validation plan', function () {
    // Simulate entity user context
    $entity = Entity::factory()->create();
    $entityUser = \App\Models\User::factory()->create(['group_id' => \App\Enums\UserGroupEnum::ENTITY->value]);
    $entityUser->entities()->attach($entity);
    $this->actingAs($entityUser);

    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create a package with ONLY non-validation affiliation plans
    $nonValidationPlan = AffiliationPlan::factory()->create([
        'name' => 'Regular Plan',
        'is_validation_plan' => false,
    ]);
    $package->affiliationPlans()->attach($nonValidationPlan);

    // Individual has no active validation plan
    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toContain('plano de validação');
});

test('entity can assign non-validation package to individual with validation plan', function () {
    // Simulate entity user context
    $entity = Entity::factory()->create();
    $entityUser = \App\Models\User::factory()->create(['group_id' => \App\Enums\UserGroupEnum::ENTITY->value]);
    $entityUser->entities()->attach($entity);
    $this->actingAs($entityUser);

    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create a package with ONLY non-validation affiliation plans
    $nonValidationPlan = AffiliationPlan::factory()->create([
        'name' => 'Regular Plan',
        'is_validation_plan' => false,
    ]);
    $package->affiliationPlans()->attach($nonValidationPlan);

    // Create an active validation plan for the individual
    $validationPlan = AffiliationPlan::factory()->create([
        'name' => 'Validation Plan',
        'is_validation_plan' => true,
    ]);

    $validationPackage = MembershipPackage::factory()->create();
    $validationPackage->affiliationPlans()->attach($validationPlan);

    $validationSubscription = MemberSubscription::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'membership_package_id' => $validationPackage->id,
    ]);

    Affiliation::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'member_subscription_id' => $validationSubscription->id,
        'federation_id' => $validationPlan->federation_id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveAffiliationState::class,
    ]);

    // Now entity should be able to assign non-validation package to individual
    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeTrue();
    expect($result['error'])->toBeNull();
});

test('individual can subscribe to package containing validation plan without restrictions', function () {
    $individual = Individual::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create a package with a validation plan
    $validationPlan = AffiliationPlan::factory()->create([
        'name' => 'Validation Plan',
        'is_validation_plan' => true,
    ]);
    $package->affiliationPlans()->attach($validationPlan);

    // Individual should be able to subscribe even without existing validation plan
    $result = $this->service->validateSubscription($individual, $package);

    expect($result['valid'])->toBeTrue();
    expect($result['error'])->toBeNull();
});

test('entity is not restricted by validation plan requirement', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create a package with ONLY non-validation affiliation plans
    $nonValidationPlan = AffiliationPlan::factory()->create([
        'name' => 'Regular Plan',
        'is_validation_plan' => false,
    ]);
    $package->affiliationPlans()->attach($nonValidationPlan);

    // Entity should not be restricted by validation plan requirement
    $result = $this->service->validateSubscription($entity, $package);

    expect($result['valid'])->toBeTrue();
    expect($result['error'])->toBeNull();
});

test('returns correct member subscription summary', function () {
    $individual = Individual::factory()->create();

    // Create active affiliation with validation plan
    $affiliationPlan = AffiliationPlan::factory()->create([
        'name' => 'Validation Plan',
        'is_validation_plan' => true,
    ]);

    $package = MembershipPackage::factory()->create();
    $package->affiliationPlans()->attach($affiliationPlan);

    $subscription = MemberSubscription::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'membership_package_id' => $package->id,
    ]);

    Affiliation::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'member_subscription_id' => $subscription->id,
        'federation_id' => $affiliationPlan->federation_id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveAffiliationState::class,
    ]);

    // Create active insurance
    $insurancePlan = InsurancePlan::factory()->create(['name' => 'Health Insurance']);
    Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan->id,
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveInsuranceState::class,
    ]);

    $summary = $this->service->getMemberSubscriptionSummary($individual);

    expect($summary['has_validation_affiliation'])->toBeTrue();
    expect($summary['has_active_insurance'])->toBeTrue();
    expect($summary['active_affiliation_plans'])->toContain('Validation Plan');
    expect($summary['active_insurance_plans'])->toContain('Health Insurance');
});
