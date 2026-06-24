<?php

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Actions\CreateInsuranceAction;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\PendingPaymentInsuranceState;
use Domain\Memberships\Actions\CreateAffiliationAction;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentAffiliationState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CreateMemberSubscriptionAction', function () {
    describe('Insurance-only packages', function () {
        test('creates insurance records for entity with insurance-only package', function () {
            // Arrange
            $entity = Entity::factory()->create();
            $insurancePlan = InsurancePlan::create([
                'name' => 'Entity Insurance Plan',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 150.00,
                'period' => 12,
                'period_unit' => 'months',
                'requires_active_affiliation' => true,
            ]);

            $package = MembershipPackage::create([
                'name' => 'Insurance Only Package for Entities',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach($insurancePlan);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert
            expect($subscription)->toBeInstanceOf(MemberSubscription::class)
                ->and($subscription->member_id)->toBe((string) $entity->id)
                ->and($subscription->membership_package_id)->toBe($package->id);

            $insurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $insurancePlan->id)
                ->first();

            expect($insurance)->not->toBeNull()
                ->and((float) $insurance->entity_fee)->toBe(150.00)
                ->and($insurance->member_subscription_id)->toBe($subscription->id)
                ->and($insurance->is_external)->toBe(false)
                ->and($insurance->status_class)->toBe(ActiveInsuranceState::class);
        });

        test('creates insurance records for individual with insurance-only package', function () {
            // Arrange
            $individual = Individual::factory()->create();
            $insurancePlan = InsurancePlan::create([
                'name' => 'Individual Insurance Plan',
                'target_audience' => 'INDIVIDUAL',
                'type' => 'personal_accident',
                'individual_fee' => 75.00,
                'period' => 12,
                'period_unit' => 'months',
                'requires_active_affiliation' => true,
            ]);

            $package = MembershipPackage::create([
                'name' => 'Insurance Only Package for Individuals',
                'is_active' => true,
                'target_type' => 'individual',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach($insurancePlan);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Individual::class,
                'member_id' => $individual->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert
            $insurance = Insurance::where('member_type', 'individual')
                ->where('member_id', $individual->id)
                ->where('insurance_plan_id', $insurancePlan->id)
                ->first();

            expect($insurance)->not->toBeNull()
                ->and((float) $insurance->individual_fee)->toBe(75.00)
                ->and($insurance->entity_fee)->toBeNull()
                ->and($insurance->member_subscription_id)->toBe($subscription->id)
                ->and($insurance->status_class)->toBe(PendingPaymentInsuranceState::class);
        });

        test('creates multiple insurance records when package has multiple insurance plans', function () {
            // Arrange
            $entity = Entity::factory()->create();

            $insurancePlan1 = InsurancePlan::create([
                'name' => 'Basic Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 100.00,
                'period' => 12,
                'period_unit' => 'months',
            ]);

            $insurancePlan2 = InsurancePlan::create([
                'name' => 'Premium Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 200.00,
                'period' => 12,
                'period_unit' => 'months',
            ]);

            $package = MembershipPackage::create([
                'name' => 'Multiple Insurance Package',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach([$insurancePlan1->id, $insurancePlan2->id]);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert
            $insurances = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('member_subscription_id', $subscription->id)
                ->get();

            expect($insurances)->toHaveCount(2)
                ->and($insurances->pluck('insurance_plan_id')->toArray())
                ->toContain($insurancePlan1->id, $insurancePlan2->id);
        });
    });

    describe('Mixed packages (affiliation + insurance)', function () {
        test('creates both affiliation and insurance records for mixed package', function () {
            // Arrange
            $entity = Entity::factory()->create();
            $federation = Federation::factory()->create();

            // Associate entity with federation
            $entity->federations()->attach($federation, [
                'status_class' => ActiveEntityFederationState::class,
            ]);

            $affiliationPlan = AffiliationPlan::create([
                'name' => 'Entity Affiliation',
                'type' => 'liability',
                'federation_id' => $federation->id,
                'entity_fee' => 50.00,
                'duration_months' => 12,
            ]);

            $insurancePlan = InsurancePlan::create([
                'name' => 'Entity Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 100.00,
                'period' => 12,
                'period_unit' => 'months',
                'requires_active_affiliation' => true,
            ]);

            $package = MembershipPackage::create([
                'name' => 'Mixed Package',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->affiliationPlans()->attach($affiliationPlan);
            $package->insurancePlans()->attach($insurancePlan);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert - Check affiliation was created
            $affiliation = Affiliation::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('member_subscription_id', $subscription->id)
                ->first();

            expect($affiliation)->not->toBeNull()
                ->and($affiliation->federation_id)->toBe($federation->id)
                ->and((float) $affiliation->entity_fee)->toBe(50.00)
                ->and($affiliation->status_class)->toBe(ActiveAffiliationState::class);

            // Assert - Check insurance was created
            $insurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $insurancePlan->id)
                ->first();

            expect($insurance)->not->toBeNull()
                ->and((float) $insurance->entity_fee)->toBe(100.00)
                ->and($insurance->status_class)->toBe(ActiveInsuranceState::class);
        });

        test('respects requires_active_affiliation setting for insurance plans', function () {
            // Arrange
            $entity = Entity::factory()->create();
            $federation = Federation::factory()->create();

            $entity->federations()->attach($federation, [
                'status_class' => ActiveEntityFederationState::class,
            ]);

            $affiliationPlan = AffiliationPlan::create([
                'name' => 'Entity Affiliation',
                'type' => 'liability',
                'federation_id' => $federation->id,
                'entity_fee' => 50.00,
                'duration_months' => 12,
            ]);

            // Insurance that doesn't require active affiliation
            $insurancePlanNoRequirement = InsurancePlan::create([
                'name' => 'No Affiliation Required Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 80.00,
                'period' => 12,
                'period_unit' => 'months',
                'requires_active_affiliation' => false,
            ]);

            // Insurance that requires active affiliation
            $insurancePlanWithRequirement = InsurancePlan::create([
                'name' => 'Affiliation Required Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 120.00,
                'period' => 12,
                'period_unit' => 'months',
                'requires_active_affiliation' => true,
            ]);

            $package = MembershipPackage::create([
                'name' => 'Mixed Package with Different Insurance Requirements',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->affiliationPlans()->attach($affiliationPlan);
            $package->insurancePlans()->attach([
                $insurancePlanNoRequirement->id,
                $insurancePlanWithRequirement->id,
            ]);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert - Both insurances should be created
            $insurances = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('member_subscription_id', $subscription->id)
                ->get();

            expect($insurances)->toHaveCount(2);
        });
    });

    describe('Edge cases and validation', function () {
        test('skips affiliation creation for entity not associated with federation', function () {
            // Arrange
            $entity = Entity::factory()->create();
            $federation1 = Federation::factory()->create();
            $federation2 = Federation::factory()->create();

            // Entity is only associated with federation1
            $entity->federations()->attach($federation1, [
                'status_class' => ActiveEntityFederationState::class,
            ]);

            // But affiliation plan is for federation2
            $affiliationPlan = AffiliationPlan::create([
                'name' => 'Federation 2 Affiliation',
                'type' => 'liability',
                'federation_id' => $federation2->id,
                'entity_fee' => 50.00,
                'duration_months' => 12,
            ]);

            $package = MembershipPackage::create([
                'name' => 'Package for Different Federation',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->affiliationPlans()->attach($affiliationPlan);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert - No affiliation should be created
            $affiliation = Affiliation::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('member_subscription_id', $subscription->id)
                ->first();

            expect($affiliation)->toBeNull();
        });

        test('handles insurance plan with policy number generation', function () {
            // Arrange
            $entity = Entity::factory()->create();

            // Group insurance plan with predefined policy number
            $groupInsurancePlan = InsurancePlan::create([
                'name' => 'Group Insurance Plan',
                'target_audience' => 'ENTITY',
                'type' => 'equipment',
                'entity_fee' => 200.00,
                'period' => 12,
                'period_unit' => 'months',
                'policy_number' => 'GROUP-POLICY-123',
            ]);

            // Sequential policy number plan
            $sequentialInsurancePlan = InsurancePlan::create([
                'name' => 'Sequential Insurance Plan',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 150.00,
                'period' => 12,
                'period_unit' => 'months',
                'policy_number_prefix' => 'SEQ',
                'policy_number_sequence' => 1000,
                'policy_number_format' => '{prefix}-{sequence}-2024',
            ]);

            $package = MembershipPackage::create([
                'name' => 'Policy Number Test Package',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach([$groupInsurancePlan->id, $sequentialInsurancePlan->id]);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert
            $groupInsurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $groupInsurancePlan->id)
                ->first();

            $sequentialInsurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $sequentialInsurancePlan->id)
                ->first();

            expect($groupInsurance->policy_number)->toBe('GROUP-POLICY-123')
                ->and($sequentialInsurance->policy_number)->toBe('SEQ-001001-2024');
        });

        test('correctly sets insurance dates based on plan configuration', function () {
            // Arrange
            $entity = Entity::factory()->create();

            $insurancePlan = InsurancePlan::create([
                'name' => 'Custom Period Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 100.00,
                'period' => 6,
                'period_unit' => 'months',
            ]);

            $package = MembershipPackage::create([
                'name' => 'Custom Period Package',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach($insurancePlan);

            $startDate = now();
            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert
            $insurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $insurancePlan->id)
                ->first();

            expect($insurance->start_date->format('Y-m-d'))->toBe($startDate->format('Y-m-d'))
                ->and($insurance->end_date->format('Y-m-d'))->toBe($startDate->copy()->addMonths(6)->format('Y-m-d'));
        });

        test('uses fixed dates from plan when start_date and end_date are defined', function () {
            // Arrange - Fixed date insurance plan (e.g., "1 Jan a 31 Dez 2025")
            $entity = Entity::factory()->create();

            $planStartDate = now()->startOfYear(); // e.g., 2025-01-01
            $planEndDate = now()->endOfYear(); // e.g., 2025-12-31

            $insurancePlan = InsurancePlan::create([
                'name' => 'Fixed Period Insurance (1 Jan a 31 Dez)',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 100.00,
                'start_date' => $planStartDate->format('Y-m-d'),
                'end_date' => $planEndDate->format('Y-m-d'),
                // No period/period_unit - fixed dates take precedence
            ]);

            $package = MembershipPackage::create([
                'name' => 'Fixed Period Package',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach($insurancePlan);

            // Subscription starts mid-year (should NOT affect insurance dates)
            $subscriptionStartDate = now()->addMonths(3); // e.g., April
            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => $subscriptionStartDate->format('Y-m-d'),
                'end_date' => $subscriptionStartDate->copy()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert - Insurance should use plan's fixed dates, NOT subscription dates
            $insurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $insurancePlan->id)
                ->first();

            expect($insurance)->not->toBeNull()
                ->and($insurance->start_date->format('Y-m-d'))->toBe($planStartDate->format('Y-m-d'))
                ->and($insurance->end_date->format('Y-m-d'))->toBe($planEndDate->format('Y-m-d'));
        });

        test('handles morph type conversion correctly', function () {
            // Arrange
            $individual = Individual::factory()->create();

            $insurancePlan = InsurancePlan::create([
                'name' => 'Individual Insurance',
                'target_audience' => 'INDIVIDUAL',
                'type' => 'personal_accident',
                'individual_fee' => 80.00,
                'period' => 12,
                'period_unit' => 'months',
            ]);

            $package = MembershipPackage::create([
                'name' => 'Individual Package',
                'is_active' => true,
                'target_type' => 'individual',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->insurancePlans()->attach($insurancePlan);

            // Test with full class name
            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => 'Domain\\Individuals\\Models\\Individual',
                'member_id' => $individual->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert
            expect($subscription->member_type)->toBe('individual');

            $insurance = Insurance::where('member_type', 'individual')
                ->where('member_id', $individual->id)
                ->first();

            expect($insurance)->not->toBeNull();
        });

        test('creates pending payment insurance and affiliation when subscription requires payment', function () {
            // Arrange
            $entity = Entity::factory()->create();
            $federation = Federation::factory()->create();

            // Associate entity with federation
            $entity->federations()->attach($federation, [
                'status_class' => ActiveEntityFederationState::class,
            ]);

            $affiliationPlan = AffiliationPlan::create([
                'name' => 'Entity Affiliation',
                'type' => 'liability',
                'federation_id' => $federation->id,
                'entity_fee' => 50.00,
                'duration_months' => 12,
            ]);

            $insurancePlan = InsurancePlan::create([
                'name' => 'Entity Insurance',
                'target_audience' => 'ENTITY',
                'type' => 'personal_accident',
                'entity_fee' => 100.00,
                'period' => 12,
                'period_unit' => 'months',
                'requires_active_affiliation' => false,
            ]);

            $package = MembershipPackage::create([
                'name' => 'Paid Package',
                'is_active' => true,
                'target_type' => 'entity',
                'distribution_methods' => ['direct'],
                'period' => 12,
                'period_unit' => 'months',
            ]);
            $package->affiliationPlans()->attach($affiliationPlan);
            $package->insurancePlans()->attach($insurancePlan);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'status_class' => PendingPaymentMemberSubscriptionState::class, // Pending payment
            ]);

            // Act
            // Mock the validation service to bypass validation in tests
            $validationService = \Mockery::mock(SubscriptionValidationService::class);
            $validationService->shouldReceive('validateSubscription')
                ->andReturn(['valid' => true, 'error' => null]);

            $action = new CreateMemberSubscriptionAction(
                new CreateAffiliationAction,
                new CreateInsuranceAction,
                $validationService
            );
            $subscription = $action($subscriptionData);

            // Assert - Check affiliation has pending payment status
            $affiliation = Affiliation::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('member_subscription_id', $subscription->id)
                ->first();

            expect($affiliation)->not->toBeNull()
                ->and($affiliation->status_class)->toBe(PendingPaymentAffiliationState::class)
                ->and($affiliation->isActive())->toBe(false);

            // Assert - Check insurance has pending payment status
            $insurance = Insurance::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('insurance_plan_id', $insurancePlan->id)
                ->first();

            expect($insurance)->not->toBeNull()
                ->and($insurance->status_class)->toBe(PendingPaymentInsuranceState::class)
                ->and($insurance->isActive())->toBe(false);
        });
    });
});
