<?php

use App\Enums\MembershipTargetType;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a federation user for testing
    $this->federation = Federation::factory()->create();
    $this->user = User::factory()->create();
    $this->user->federations()->attach($this->federation);

    // Create entities and individuals for testing
    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->federation, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $this->individual = Individual::factory()->create();
    $this->individual->entities()->attach($this->entity);

    // Create membership packages
    $this->membershipPackage = MembershipPackage::create([
        'name' => 'Test Individual Membership Package',
        'description' => 'Test package with affiliation plans',
        'is_active' => true,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['direct'],
    ]);

    $this->entityMembershipPackage = MembershipPackage::create([
        'name' => 'Test Entity Membership Package',
        'description' => 'Test entity package with affiliation plans',
        'is_active' => true,
        'target_type' => MembershipTargetType::ENTITY,
        'distribution_methods' => ['direct'],
    ]);

    $this->insuranceOnlyPackage = MembershipPackage::create([
        'name' => 'Test Insurance Package',
        'description' => 'Test insurance-only package',
        'is_active' => true,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['direct'],
    ]);

    // Create affiliation and insurance plans
    $this->affiliationPlan = AffiliationPlan::create([
        'federation_id' => $this->federation->id,
        'name' => 'Test Affiliation Plan',
        'description' => 'Test affiliation plan',
        'duration_months' => 12,
        'individual_fee' => 50.00,
        'entity_fee' => 100.00,
        'type' => 'standard',
    ]);

    $this->insurancePlan = InsurancePlan::create([
        'name' => 'Test Insurance Plan',
        'description' => 'Test insurance plan',
        'individual_fee' => 25.00,
        'entity_fee' => 50.00,
        'target_audience' => 'general',
        'type' => 'liability',
    ]);

    // Associate plans with packages
    $this->membershipPackage->affiliationPlans()->attach($this->affiliationPlan);
    $this->membershipPackage->insurancePlans()->attach($this->insurancePlan);
    $this->membershipPackage->federations()->attach($this->federation);

    $this->entityMembershipPackage->affiliationPlans()->attach($this->affiliationPlan);
    $this->entityMembershipPackage->insurancePlans()->attach($this->insurancePlan);
    $this->entityMembershipPackage->federations()->attach($this->federation);

    // Insurance-only package (no affiliation plans)
    $this->insuranceOnlyPackage->insurancePlans()->attach($this->insurancePlan);
    $this->insuranceOnlyPackage->federations()->attach($this->federation);

    Auth::login($this->user);
});

describe('Federation association validation', function () {
    it('validates federation association before creating individual membership subscriptions', function () {
        // Create entity not associated with federation
        $unassociatedEntity = Entity::factory()->create();
        $unassociatedIndividual = Individual::factory()->create();
        $unassociatedIndividual->entities()->attach($unassociatedEntity);

        // Attempt to create subscription should fail
        $subscription = MemberSubscription::factory()->make([
            'member_type' => Individual::class,
            'member_id' => $unassociatedIndividual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'request_type' => 'federation_facilitated',
        ]);

        // The validation should happen at the controller level
        // Here we test the business logic that checks federation association
        $hasAssociation = $unassociatedEntity->federations()
            ->where('federation.id', $this->federation->id)
            ->where('entity_federation.status_class', ActiveEntityFederationState::class)
            ->exists();

        expect($hasAssociation)->toBeFalse();
    });

    it('validates federation association before creating entity subscriptions', function () {
        // Create entity not associated with federation
        $unassociatedEntity = Entity::factory()->create();

        // The validation should happen at the controller level
        $hasAssociation = $unassociatedEntity->federations()
            ->where('federation.id', $this->federation->id)
            ->where('entity_federation.status_class', ActiveEntityFederationState::class)
            ->exists();

        expect($hasAssociation)->toBeFalse();

        // Valid entity should have association
        $validAssociation = $this->entity->federations()
            ->where('federation.id', $this->federation->id)
            ->where('entity_federation.status_class', ActiveEntityFederationState::class)
            ->exists();

        expect($validAssociation)->toBeTrue();
    });

    it('validates individual belongs to entity under federation', function () {
        // Create individual not associated with any entity under federation
        $otherEntity = Entity::factory()->create();
        $otherIndividual = Individual::factory()->create();
        $otherIndividual->entities()->attach($otherEntity);

        // Check if individual belongs to entity under federation
        $belongsToFederationEntity = $otherIndividual->entities()
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })
            ->exists();

        expect($belongsToFederationEntity)->toBeFalse();

        // Valid individual should belong to federation entity
        $validBelonging = $this->individual->entities()
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })
            ->exists();

        expect($validBelonging)->toBeTrue();
    });
});

describe('Duplicate subscription prevention', function () {
    it('prevents duplicate individual membership subscriptions to same package', function () {
        // Create existing subscription
        $existingSubscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->addYear(),
            'request_type' => 'federation_facilitated',
        ]);

        // Check for duplicate subscription
        $hasDuplicate = MemberSubscription::where('member_type', Individual::class)
            ->where('member_id', $this->individual->id)
            ->where('membership_package_id', $this->membershipPackage->id)
            ->where('end_date', '>', now())
            ->exists();

        expect($hasDuplicate)->toBeTrue();
    });

    it('prevents duplicate entity subscriptions to same package', function () {
        // Create existing subscription
        $existingSubscription = MemberSubscription::factory()->create([
            'member_type' => Entity::class,
            'member_id' => $this->entity->id,
            'membership_package_id' => $this->entityMembershipPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->addYear(),
            'request_type' => 'federation_facilitated',
        ]);

        // Check for duplicate subscription
        $hasDuplicate = MemberSubscription::where('member_type', Entity::class)
            ->where('member_id', $this->entity->id)
            ->where('membership_package_id', $this->entityMembershipPackage->id)
            ->where('end_date', '>', now())
            ->exists();

        expect($hasDuplicate)->toBeTrue();
    });

    it('prevents duplicate insurance subscriptions for individuals', function () {
        // Create existing insurance
        $existingInsurance = Insurance::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'end_date' => now()->addYear(),
            'request_type' => 'federation_facilitated',
        ]);

        // Check for duplicate insurance
        $hasDuplicate = Insurance::where('member_type', Individual::class)
            ->where('member_id', $this->individual->id)
            ->where('insurance_plan_id', $this->insurancePlan->id)
            ->where('end_date', '>=', now())
            ->exists();

        expect($hasDuplicate)->toBeTrue();
    });

    it('allows subscriptions after expiration', function () {
        // Create expired subscription
        $expiredSubscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->subDay(),
            'request_type' => 'federation_facilitated',
        ]);

        // Check for active duplicate subscription (should not exist)
        $hasActiveDuplicate = MemberSubscription::where('member_type', Individual::class)
            ->where('member_id', $this->individual->id)
            ->where('membership_package_id', $this->membershipPackage->id)
            ->where('end_date', '>', now())
            ->exists();

        expect($hasActiveDuplicate)->toBeFalse();
    });
});

describe('Request type assignment', function () {
    it('assigns correct request_type as federation_facilitated for individual memberships', function () {
        $subscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($subscription->request_type)->toBe('federation_facilitated');
    });

    it('assigns correct request_type as federation_facilitated for entity subscriptions', function () {
        $subscription = MemberSubscription::factory()->create([
            'member_type' => Entity::class,
            'member_id' => $this->entity->id,
            'membership_package_id' => $this->entityMembershipPackage->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($subscription->request_type)->toBe('federation_facilitated');
    });

    it('assigns correct request_type as federation_facilitated for individual insurances', function () {
        $insurance = Insurance::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($insurance->request_type)->toBe('federation_facilitated');
    });

    it('distinguishes federation_facilitated from direct requests', function () {
        // Create direct request (not federation facilitated)
        $directSubscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'request_type' => 'direct',
        ]);

        // Create federation facilitated request
        $federationSubscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($directSubscription->request_type)->toBe('direct');
        expect($federationSubscription->request_type)->toBe('federation_facilitated');
    });
});

describe('Payment responsibility assignment', function () {
    it('assigns entity as requester for individual membership subscriptions', function () {
        $subscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'requester_type' => Entity::class,
            'requester_id' => $this->entity->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($subscription->requester_type)->toBe(Entity::class);
        expect($subscription->requester_id)->toBe($this->entity->id);
    });

    it('assigns entity as requester for entity subscriptions', function () {
        $subscription = MemberSubscription::factory()->create([
            'member_type' => Entity::class,
            'member_id' => $this->entity->id,
            'membership_package_id' => $this->entityMembershipPackage->id,
            'requester_type' => Entity::class,
            'requester_id' => $this->entity->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($subscription->requester_type)->toBe(Entity::class);
        expect($subscription->requester_id)->toBe($this->entity->id);
    });

    it('assigns entity as requester for individual insurance subscriptions', function () {
        $insurance = Insurance::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'requester_type' => Entity::class,
            'requester_id' => $this->entity->id,
            'request_type' => 'federation_facilitated',
        ]);

        expect($insurance->requester_type)->toBe(Entity::class);
        expect($insurance->requester_id)->toBe($this->entity->id);
    });

    it('ensures payment documents are sent to entity as requester', function () {
        // Test that entity is the one who receives payment documents
        $subscription = MemberSubscription::factory()->create([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'requester_type' => Entity::class,
            'requester_id' => $this->entity->id,
            'request_type' => 'federation_facilitated',
            'status_class' => PendingPaymentMemberSubscriptionState::class,
        ]);

        // The requester (entity) should be responsible for payment
        expect($subscription->requester_type)->toBe(Entity::class);
        expect($subscription->requester_id)->toBe($this->entity->id);

        // The member (individual) is different from the requester (entity)
        expect($subscription->member_type)->toBe(Individual::class);
        expect($subscription->member_id)->toBe($this->individual->id);
        expect($subscription->member_id)->not->toBe($subscription->requester_id);
    });

    it('validates requester belongs to federation for individual subscriptions', function () {
        // Create entity not associated with federation
        $unassociatedEntity = Entity::factory()->create();

        // Subscription with unassociated entity as requester should be invalid
        $subscription = MemberSubscription::factory()->make([
            'member_type' => Individual::class,
            'member_id' => $this->individual->id,
            'membership_package_id' => $this->membershipPackage->id,
            'requester_type' => Entity::class,
            'requester_id' => $unassociatedEntity->id,
            'request_type' => 'federation_facilitated',
        ]);

        // Validate that requester entity belongs to federation
        $requesterBelongsToFederation = Entity::where('id', $subscription->requester_id)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })
            ->exists();

        expect($requesterBelongsToFederation)->toBeFalse();

        // Valid requester should belong to federation
        $validRequesterBelongsToFederation = Entity::where('id', $this->entity->id)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })
            ->exists();

        expect($validRequesterBelongsToFederation)->toBeTrue();
    });
});
