<?php

use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Memberships\Actions\ActivateMemberSubscriptionAction;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\InactiveAffiliationState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('ActivateMemberSubscriptionAction', function () {
    describe('Federation sync on affiliation activation', function () {
        test('activating subscription syncs individual to affiliation federation', function () {
            // Arrange
            $federation = Federation::factory()->create(['is_local' => true]);
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Reload with affiliations
            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert
            expect(IndividualFederation::where('individual_id', $individual->id)
                ->where('federation_id', $federation->id)
                ->where('status_class', ActiveIndividualFederationState::class)
                ->exists()
            )->toBeTrue();
        });

        test('activating subscription activates existing pending federation membership', function () {
            // Arrange
            $federation = Federation::factory()->create(['is_local' => true]);
            $individual = Individual::factory()->create();

            // Create existing pending federation membership
            IndividualFederation::create([
                'individual_id' => $individual->id,
                'federation_id' => $federation->id,
                'status_class' => PendingIndividualFederationState::class,
                'active' => 0,
            ]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Reload with affiliations
            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - should now be active
            $individualFederation = IndividualFederation::where('individual_id', $individual->id)
                ->where('federation_id', $federation->id)
                ->first();

            expect($individualFederation)->not->toBeNull()
                ->and($individualFederation->status_class)->toBe(ActiveIndividualFederationState::class)
                ->and($individualFederation->active)->toBe(1);
        });

        test('does not duplicate federation membership if already active', function () {
            // Arrange
            $federation = Federation::factory()->create(['is_local' => true]);
            $individual = Individual::factory()->create();

            // Create existing active federation membership
            IndividualFederation::create([
                'individual_id' => $individual->id,
                'federation_id' => $federation->id,
                'status_class' => ActiveIndividualFederationState::class,
                'active' => 1,
            ]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Reload with affiliations
            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - should still be only one record
            $count = IndividualFederation::where('individual_id', $individual->id)
                ->where('federation_id', $federation->id)
                ->count();

            expect($count)->toBe(1);
        });

        test('does not sync entity subscription to individual federation table', function () {
            // Arrange
            $federation = Federation::factory()->create(['is_local' => true]);
            $entity = \Domain\Entities\Models\Entity::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'entity',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'entity',
                'member_id' => $entity->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'entity',
                'member_id' => $entity->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Reload with affiliations
            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - no individual federation record should be created
            expect(IndividualFederation::count())->toBe(0);
        });

        test('activates affiliation status when subscription is activated', function () {
            // Arrange
            $federation = Federation::factory()->create();
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $affiliation = Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Reload with affiliations
            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert
            $affiliation->refresh();
            expect($affiliation->status_class)->toBe(ActiveAffiliationState::class);
        });

        test('handles subscription with multiple affiliations to different federations', function () {
            // Arrange
            $federation1 = Federation::factory()->create(['is_local' => true]);
            $federation2 = Federation::factory()->create(['is_local' => false]);
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation1->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation2->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Reload with affiliations
            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - both federations should be synced
            expect(IndividualFederation::where('individual_id', $individual->id)
                ->where('federation_id', $federation1->id)
                ->where('status_class', ActiveIndividualFederationState::class)
                ->exists()
            )->toBeTrue()
                ->and(IndividualFederation::where('individual_id', $individual->id)
                    ->where('federation_id', $federation2->id)
                    ->where('status_class', ActiveIndividualFederationState::class)
                    ->exists()
                )->toBeTrue();
        });

        test('activating subscription changes rejected federation membership to active', function () {
            // Arrange
            $federation = Federation::factory()->create();
            $individual = Individual::factory()->create();

            // Create existing REJECTED federation membership
            IndividualFederation::create([
                'individual_id' => $individual->id,
                'federation_id' => $federation->id,
                'status_class' => \Domain\Individuals\States\RejectedIndividualFederationState::class,
                'active' => 0,
                'rejected_at' => now(),
            ]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            $subscription->load('affiliations');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - Federation membership should now be active
            $individualFederation = IndividualFederation::where('individual_id', $individual->id)
                ->where('federation_id', $federation->id)
                ->first();

            expect($individualFederation->status_class)->toBe(ActiveIndividualFederationState::class)
                ->and($individualFederation->active)->toBe(1);
        });
    });

    describe('Subscription activation state changes', function () {
        test('updates subscription status to active', function () {
            // Arrange
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert
            $subscription->refresh();
            expect($subscription->status_class)->toBe(ActiveMemberSubscriptionState::class);
        });

        test('does not activate subscription that is not in pending payment state', function () {
            // Arrange
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => ActiveMemberSubscriptionState::class, // Already active
            ]);

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - should still be active (not changed)
            $subscription->refresh();
            expect($subscription->status_class)->toBe(ActiveMemberSubscriptionState::class);
        });
    });

    describe('Insurance activation on subscription activation', function () {
        test('activating subscription activates related insurance', function () {
            // Arrange
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $insurancePlan = \Domain\Insurance\Models\InsurancePlan::factory()->create();

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $insurance = \Domain\Insurance\Models\Insurance::factory()->create([
                'insurance_plan_id' => $insurancePlan->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'member_subscription_id' => $subscription->id,
                'status_class' => \Domain\Insurance\States\PendingPaymentInsuranceState::class,
            ]);

            // Reload with insurances
            $subscription->load('insurances');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert
            $insurance->refresh();
            expect($insurance->status_class)->toBe(\Domain\Insurance\States\ActiveInsuranceState::class);
        });

        test('activating subscription activates multiple insurances', function () {
            // Arrange
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $insurancePlan1 = \Domain\Insurance\Models\InsurancePlan::factory()->create();
            $insurancePlan2 = \Domain\Insurance\Models\InsurancePlan::factory()->create();

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $insurance1 = \Domain\Insurance\Models\Insurance::factory()->create([
                'insurance_plan_id' => $insurancePlan1->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'member_subscription_id' => $subscription->id,
                'status_class' => \Domain\Insurance\States\PendingPaymentInsuranceState::class,
            ]);

            $insurance2 = \Domain\Insurance\Models\Insurance::factory()->create([
                'insurance_plan_id' => $insurancePlan2->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'member_subscription_id' => $subscription->id,
                'status_class' => \Domain\Insurance\States\PendingPaymentInsuranceState::class,
            ]);

            // Reload with insurances
            $subscription->load('insurances');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert
            $insurance1->refresh();
            $insurance2->refresh();
            expect($insurance1->status_class)->toBe(\Domain\Insurance\States\ActiveInsuranceState::class)
                ->and($insurance2->status_class)->toBe(\Domain\Insurance\States\ActiveInsuranceState::class);
        });

        test('does not re-activate already active insurance', function () {
            // Arrange
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $insurancePlan = \Domain\Insurance\Models\InsurancePlan::factory()->create();

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            // Create insurance that is already active
            $insurance = \Domain\Insurance\Models\Insurance::factory()->create([
                'insurance_plan_id' => $insurancePlan->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'member_subscription_id' => $subscription->id,
                'status_class' => \Domain\Insurance\States\ActiveInsuranceState::class,
            ]);

            // Reload with insurances
            $subscription->load('insurances');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - should still be active (unchanged)
            $insurance->refresh();
            expect($insurance->status_class)->toBe(\Domain\Insurance\States\ActiveInsuranceState::class);
        });

        test('activates insurance for entity subscription', function () {
            // Arrange
            $entity = \Domain\Entities\Models\Entity::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'entity',
            ]);

            $insurancePlan = \Domain\Insurance\Models\InsurancePlan::factory()->create();

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'entity',
                'member_id' => $entity->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $insurance = \Domain\Insurance\Models\Insurance::factory()->create([
                'insurance_plan_id' => $insurancePlan->id,
                'member_type' => 'entity',
                'member_id' => $entity->id,
                'member_subscription_id' => $subscription->id,
                'status_class' => \Domain\Insurance\States\PendingPaymentInsuranceState::class,
            ]);

            // Reload with insurances
            $subscription->load('insurances');

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert
            $insurance->refresh();
            expect($insurance->status_class)->toBe(\Domain\Insurance\States\ActiveInsuranceState::class);
        });

        test('activates both affiliations and insurances in mixed subscription', function () {
            // Arrange
            $federation = Federation::factory()->create();
            $individual = Individual::factory()->create();

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $insurancePlan = \Domain\Insurance\Models\InsurancePlan::factory()->create();

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            // Create affiliation
            $affiliation = Affiliation::factory()->create([
                'member_subscription_id' => $subscription->id,
                'federation_id' => $federation->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => InactiveAffiliationState::class,
            ]);

            // Create insurance
            $insurance = \Domain\Insurance\Models\Insurance::factory()->create([
                'insurance_plan_id' => $insurancePlan->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'member_subscription_id' => $subscription->id,
                'status_class' => \Domain\Insurance\States\PendingPaymentInsuranceState::class,
            ]);

            // Reload with both relationships
            $subscription->load(['affiliations', 'insurances']);

            // Act
            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            // Assert - Both should be activated
            $affiliation->refresh();
            $insurance->refresh();

            expect($affiliation->status_class)->toBe(ActiveAffiliationState::class)
                ->and($insurance->status_class)->toBe(\Domain\Insurance\States\ActiveInsuranceState::class);

            // And individual should be synced to federation
            expect(IndividualFederation::where('individual_id', $individual->id)
                ->where('federation_id', $federation->id)
                ->where('status_class', ActiveIndividualFederationState::class)
                ->exists()
            )->toBeTrue();
        });
    });

    describe('Member number assignment on activation', function () {
        beforeEach(function () {
            DB::table('member_number_settings')->updateOrInsert(
                ['key' => 'individual_counter'],
                ['value' => 1000, 'description' => 'Individual counter', 'created_at' => now(), 'updated_at' => now()]
            );
        });

        test('assigns member number to individual without one on activation and advances counter', function () {
            $individual = Individual::factory()->create(['member_number' => null]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            $individual->refresh();
            expect($individual->member_number)->toBe(1000);

            $counter = DB::table('member_number_settings')->where('key', 'individual_counter')->first();
            expect((int) $counter->value)->toBe(1001);
        });

        test('skips collision when counter value is already taken by another individual', function () {
            Individual::factory()->create(['member_number' => 1000]);

            $individual = Individual::factory()->create(['member_number' => null]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            $individual->refresh();
            expect($individual->member_number)->toBe(1001);

            $counter = DB::table('member_number_settings')->where('key', 'individual_counter')->first();
            expect((int) $counter->value)->toBe(1002);
        });

        test('does not change existing member number on activation', function () {
            $individual = Individual::factory()->create(['member_number' => 42]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'individual',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            $individual->refresh();
            expect($individual->member_number)->toBe(42);

            $counter = DB::table('member_number_settings')->where('key', 'individual_counter')->first();
            expect((int) $counter->value)->toBe(1000);
        });

        test('does not assign member number for entity subscriptions', function () {
            DB::table('member_number_settings')->updateOrInsert(
                ['key' => 'entity_counter'],
                ['value' => 1000, 'description' => 'Entity counter', 'created_at' => now(), 'updated_at' => now()]
            );

            $entity = \Domain\Entities\Models\Entity::factory()->create(['member_number' => null]);

            $package = MembershipPackage::factory()->create([
                'target_type' => 'entity',
            ]);

            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'entity',
                'member_id' => $entity->id,
                'membership_package_id' => $package->id,
                'status_class' => PendingPaymentMemberSubscriptionState::class,
            ]);

            $action = new ActivateMemberSubscriptionAction;
            $action($subscription->id);

            $entity->refresh();
            expect($entity->member_number)->toBeNull();

            $counter = DB::table('member_number_settings')->where('key', 'individual_counter')->first();
            expect((int) $counter->value)->toBe(1000);
        });
    });
});
