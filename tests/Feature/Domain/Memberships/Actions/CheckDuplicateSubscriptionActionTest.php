<?php

use Domain\Entities\Models\Entity;
use Domain\Memberships\Actions\CheckDuplicateSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\ExpiredMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it detects active duplicate subscriptions', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create active subscription
    MemberSubscription::factory()->create([
        'member_type' => Entity::class,
        'member_id' => $entity->id,
        'membership_package_id' => $package->id,
        'end_date' => now()->addDays(30),
        'status_class' => ActiveMemberSubscriptionState::class,
    ]);

    $action = new CheckDuplicateSubscriptionAction;
    $result = $action->execute($entity, $package);

    expect($result)->toBeTrue();
});

test('it detects pending payment duplicate subscriptions', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create pending payment subscription
    MemberSubscription::factory()->create([
        'member_type' => Entity::class,
        'member_id' => $entity->id,
        'membership_package_id' => $package->id,
        'end_date' => now()->addDays(30),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    $action = new CheckDuplicateSubscriptionAction;
    $result = $action->execute($entity, $package);

    expect($result)->toBeTrue();
});

test('it allows subscription when no duplicate exists', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    // No existing subscriptions

    $action = new CheckDuplicateSubscriptionAction;
    $result = $action->execute($entity, $package);

    expect($result)->toBeFalse();
});

test('it ignores expired subscriptions', function () {
    $entity = Entity::factory()->create();
    $package = MembershipPackage::factory()->create();

    // Create expired subscription
    MemberSubscription::factory()->create([
        'member_type' => Entity::class,
        'member_id' => $entity->id,
        'membership_package_id' => $package->id,
        'end_date' => now()->subDays(1),
        'status_class' => ExpiredMemberSubscriptionState::class,
    ]);

    $action = new CheckDuplicateSubscriptionAction;
    $result = $action->execute($entity, $package);

    expect($result)->toBeFalse();
});
