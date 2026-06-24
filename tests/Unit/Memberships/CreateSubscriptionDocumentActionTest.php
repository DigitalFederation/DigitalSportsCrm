<?php

use App\Enums\UserGroupEnum;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    DocumentType::factory()->create(['code' => 'ORD']);
});

it('creates document with correct fees for entity-managed individual subscription', function () {
    // Create entity user
    $entityUser = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);
    $entity = Entity::factory()->create();
    $entityUser->entities()->attach($entity);

    // Create individual
    $individual = Individual::factory()->create();

    // Create federation
    $federation = Federation::factory()->create();

    // Create insurance plan with both fees - entity will pay entity_fee
    $insurancePlan = InsurancePlan::create([
        'name' => 'Test Insurance',
        'target_audience' => 'INDIVIDUAL',
        'type' => 'personal_accident',
        'individual_fee' => 100.00,
        'entity_fee' => 150.00, // Entity pays more when buying for members
        'vat_rate' => 23,
        'period' => 12,
        'period_unit' => 'month',
        'description' => 'Test insurance plan',
    ]);

    // Create package for individuals with entity_managed distribution
    $package = MembershipPackage::create([
        'name' => 'Test Package',
        'target_type' => 'individual',
        'distribution_methods' => ['entity_managed'],
        'is_active' => true,
    ]);

    // Attach insurance to package
    $package->insurancePlans()->attach($insurancePlan);

    // Create subscription for individual with entity as requester (entity-managed)
    $subscription = MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'individual_id' => $individual->id,
        'requester_type' => 'entity',  // Entity is paying
        'requester_id' => $entity->id,  // Entity ID
        'request_type' => 'entity_managed',  // Entity-managed subscription
        'status_class' => PendingPaymentMemberSubscriptionState::class,
        'start_date' => now(),
        'end_date' => now()->addYear(),
    ]);

    // Act as entity user
    $this->actingAs($entityUser);

    // Execute document creation
    $action = new CreateSubscriptionDocumentAction;
    $document = $action->execute($subscription);

    // Assert document was created
    expect($document)->toBeInstanceOf(Document::class);

    // Assert document has correct total (150 + 23% tax = 184.50)
    expect($document->total_value)->toBe(184.50);

    // Assert document is assigned to the entity (entity-managed subscription)
    expect($document->owner_type)->toBe('entity');
    expect($document->owner_id)->toBe((string) $entity->id);
});

it('creates document with entity fees when entity subscribes directly', function () {
    // Create entity user
    $entityUser = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);
    $entity = Entity::factory()->create();
    $entityUser->entities()->attach($entity);

    // Create federation
    $federation = Federation::factory()->create();

    // Create affiliation plan with entity fee
    $affiliationPlan = AffiliationPlan::create([
        'federation_id' => $federation->id,
        'name' => 'Test Affiliation',
        'individual_fee' => 50.00,
        'entity_fee' => 200.00,
        'vat_rate' => 23,
        'description' => 'Test affiliation plan',
    ]);

    // Create package for entities
    $package = MembershipPackage::create([
        'name' => 'Entity Package',
        'target_type' => 'entity',
        'distribution_methods' => ['direct'],
        'is_active' => true,
    ]);

    // Attach affiliation to package
    $package->affiliationPlans()->attach($affiliationPlan);

    // Create subscription for entity
    $subscription = MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => Entity::class,
        'member_id' => $entity->id,
        'entity_id' => $entity->id,
        'status_class' => PendingPaymentMemberSubscriptionState::class,
        'start_date' => now(),
        'end_date' => now()->addYear(),
    ]);

    // Act as entity user
    $this->actingAs($entityUser);

    // Execute document creation
    $action = new CreateSubscriptionDocumentAction;
    $document = $action->execute($subscription);

    // Assert document was created
    expect($document)->toBeInstanceOf(Document::class);

    // Assert document has correct total (200 + 23% tax = 246)
    expect($document->total_value)->toBe(246.0);

    // Assert document is assigned to the entity
    expect($document->owner_type)->toBe('entity');
    expect($document->owner_id)->toBe((string) $entity->id);
});

it('creates document with individual fees when individual subscribes directly', function () {
    // Create individual user
    $individualUser = User::factory()->create(['group_id' => UserGroupEnum::INDIVIDUAL->value]);
    $individual = Individual::factory()->create();
    $individual->update(['user_id' => $individualUser->id]);

    // Create federation
    $federation = Federation::factory()->create();

    // Create insurance plan with both fees
    $insurancePlan = InsurancePlan::create([
        'name' => 'Test Insurance',
        'target_audience' => 'INDIVIDUAL',
        'type' => 'personal_accident',
        'individual_fee' => 100.00,
        'entity_fee' => 150.00,
        'vat_rate' => 23,
        'period' => 12,
        'period_unit' => 'month',
        'description' => 'Test insurance plan',
    ]);

    // Create package for individuals with direct distribution
    $package = MembershipPackage::create([
        'name' => 'Individual Package',
        'target_type' => 'individual',
        'distribution_methods' => ['direct'],
        'is_active' => true,
    ]);

    // Attach insurance to package
    $package->insurancePlans()->attach($insurancePlan);

    // Create subscription for individual
    $subscription = MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'individual_id' => $individual->id,
        'status_class' => PendingPaymentMemberSubscriptionState::class,
        'start_date' => now(),
        'end_date' => now()->addYear(),
    ]);

    // Act as individual user
    $this->actingAs($individualUser);

    // Execute document creation
    $action = new CreateSubscriptionDocumentAction;
    $document = $action->execute($subscription);

    // Assert document was created
    expect($document)->toBeInstanceOf(Document::class);

    // Assert document has correct total (100 + 23% tax = 123) - individual fee
    expect($document->total_value)->toBe(123.0);

    // Assert document is assigned to the individual
    expect($document->owner_type)->toBe('individual');
    expect($document->owner_id)->toBe((string) $individual->id);
});

it('includes moloni_reference in document details from affiliation plan', function () {
    // Create individual user
    $individualUser = User::factory()->create(['group_id' => UserGroupEnum::INDIVIDUAL->value]);
    $individual = Individual::factory()->create();
    $individual->update(['user_id' => $individualUser->id]);

    // Create federation
    $federation = Federation::factory()->create();

    // Create affiliation plan with moloni_reference
    $affiliationPlan = AffiliationPlan::create([
        'federation_id' => $federation->id,
        'name' => 'Test Affiliation with Moloni Ref',
        'individual_fee' => 50.00,
        'vat_rate' => 23,
        'description' => 'Test affiliation plan',
        'moloni_reference' => 'AFF-PLAN-001',
    ]);

    // Create package for individuals
    $package = MembershipPackage::create([
        'name' => 'Individual Package',
        'target_type' => 'individual',
        'distribution_methods' => ['direct'],
        'is_active' => true,
    ]);

    // Attach affiliation to package
    $package->affiliationPlans()->attach($affiliationPlan);

    // Create subscription for individual
    $subscription = MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'individual_id' => $individual->id,
        'status_class' => PendingPaymentMemberSubscriptionState::class,
        'start_date' => now(),
        'end_date' => now()->addYear(),
    ]);

    // Act as individual user
    $this->actingAs($individualUser);

    // Execute document creation
    $action = new CreateSubscriptionDocumentAction;
    $document = $action->execute($subscription);

    // Assert document was created
    expect($document)->toBeInstanceOf(Document::class);

    // Assert document detail has the moloni_reference
    $detail = $document->details->first();
    expect($detail->reference)->toBe('AFF-PLAN-001');
});

it('includes moloni_reference in document details from insurance plan', function () {
    // Create individual user
    $individualUser = User::factory()->create(['group_id' => UserGroupEnum::INDIVIDUAL->value]);
    $individual = Individual::factory()->create();
    $individual->update(['user_id' => $individualUser->id]);

    // Create insurance plan with moloni_reference
    $insurancePlan = InsurancePlan::create([
        'name' => 'Test Insurance with Moloni Ref',
        'target_audience' => 'INDIVIDUAL',
        'type' => 'personal_accident',
        'individual_fee' => 100.00,
        'vat_rate' => 23,
        'period' => 12,
        'period_unit' => 'month',
        'description' => 'Test insurance plan',
        'moloni_reference' => 'INS-PLAN-001',
    ]);

    // Create package for individuals
    $package = MembershipPackage::create([
        'name' => 'Individual Package',
        'target_type' => 'individual',
        'distribution_methods' => ['direct'],
        'is_active' => true,
    ]);

    // Attach insurance to package
    $package->insurancePlans()->attach($insurancePlan);

    // Create subscription for individual
    $subscription = MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'individual_id' => $individual->id,
        'status_class' => PendingPaymentMemberSubscriptionState::class,
        'start_date' => now(),
        'end_date' => now()->addYear(),
    ]);

    // Act as individual user
    $this->actingAs($individualUser);

    // Execute document creation
    $action = new CreateSubscriptionDocumentAction;
    $document = $action->execute($subscription);

    // Assert document was created
    expect($document)->toBeInstanceOf(Document::class);

    // Assert document detail has the moloni_reference
    $detail = $document->details->first();
    expect($detail->reference)->toBe('INS-PLAN-001');
});

it('handles null moloni_reference in plans gracefully', function () {
    // Create individual user
    $individualUser = User::factory()->create(['group_id' => UserGroupEnum::INDIVIDUAL->value]);
    $individual = Individual::factory()->create();
    $individual->update(['user_id' => $individualUser->id]);

    // Create federation
    $federation = Federation::factory()->create();

    // Create affiliation plan without moloni_reference
    $affiliationPlan = AffiliationPlan::create([
        'federation_id' => $federation->id,
        'name' => 'Test Affiliation no Moloni Ref',
        'individual_fee' => 50.00,
        'vat_rate' => 23,
        'description' => 'Test affiliation plan',
        'moloni_reference' => null,
    ]);

    // Create package for individuals
    $package = MembershipPackage::create([
        'name' => 'Individual Package',
        'target_type' => 'individual',
        'distribution_methods' => ['direct'],
        'is_active' => true,
    ]);

    // Attach affiliation to package
    $package->affiliationPlans()->attach($affiliationPlan);

    // Create subscription for individual
    $subscription = MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'individual_id' => $individual->id,
        'status_class' => PendingPaymentMemberSubscriptionState::class,
        'start_date' => now(),
        'end_date' => now()->addYear(),
    ]);

    // Act as individual user
    $this->actingAs($individualUser);

    // Execute document creation
    $action = new CreateSubscriptionDocumentAction;
    $document = $action->execute($subscription);

    // Assert document was created
    expect($document)->toBeInstanceOf(Document::class);

    // Assert document detail has null reference
    $detail = $document->details->first();
    expect($detail->reference)->toBeNull();
});
