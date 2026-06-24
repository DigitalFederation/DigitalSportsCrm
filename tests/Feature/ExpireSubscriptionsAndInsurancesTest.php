<?php

use Domain\Federations\Models\Federation;
use Domain\Insurance\Actions\ExpireInsuranceAction;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\ExpiredInsuranceState;
use Domain\Insurance\States\InactiveInsuranceState;
use Domain\Memberships\Actions\ExpireMemberSubscriptionAction;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\ExpiredMemberSubscriptionState;
use Domain\Memberships\States\PendingMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    Federation::factory()->create(['is_default_federation' => true, 'is_local' => false]);
});

// --- MemberSubscription expiration ---

it('expires an active member subscription past its end date', function () {
    $subscription = MemberSubscription::factory()->create([
        'status_class' => ActiveMemberSubscriptionState::class,
        'end_date' => now()->subDay(),
    ]);

    $action = new ExpireMemberSubscriptionAction;
    $action($subscription);

    $subscription->refresh();
    expect($subscription->status_class)->toBe(ExpiredMemberSubscriptionState::class);
});

it('throws exception when trying to expire a non-active member subscription', function () {
    $subscription = MemberSubscription::factory()->create([
        'status_class' => PendingMemberSubscriptionState::class,
        'end_date' => now()->subDay(),
    ]);

    $action = new ExpireMemberSubscriptionAction;
    $action($subscription);
})->throws(\Exception::class, 'Subscription must be in Active state to expire');

it('does not expire active member subscriptions that have not reached their end date via command', function () {
    MemberSubscription::factory()->create([
        'status_class' => ActiveMemberSubscriptionState::class,
        'end_date' => now()->addMonth(),
    ]);

    artisan('command:ExpireMemberSubscriptions')->assertSuccessful();

    expect(MemberSubscription::where('status_class', ActiveMemberSubscriptionState::class)->count())->toBe(1);
    expect(MemberSubscription::where('status_class', ExpiredMemberSubscriptionState::class)->count())->toBe(0);
});

it('expires only active member subscriptions past their end date via command', function () {
    // Should be expired
    MemberSubscription::factory()->create([
        'status_class' => ActiveMemberSubscriptionState::class,
        'end_date' => now()->subDays(10),
    ]);

    // Should NOT be expired (still active, future date)
    MemberSubscription::factory()->create([
        'status_class' => ActiveMemberSubscriptionState::class,
        'end_date' => now()->addMonth(),
    ]);

    // Should NOT be expired (already pending)
    MemberSubscription::factory()->create([
        'status_class' => PendingMemberSubscriptionState::class,
        'end_date' => now()->subDays(10),
    ]);

    artisan('command:ExpireMemberSubscriptions')->assertSuccessful();

    expect(MemberSubscription::where('status_class', ExpiredMemberSubscriptionState::class)->count())->toBe(1);
    expect(MemberSubscription::where('status_class', ActiveMemberSubscriptionState::class)->count())->toBe(1);
    expect(MemberSubscription::where('status_class', PendingMemberSubscriptionState::class)->count())->toBe(1);
});

it('logs activity when member subscription is expired', function () {
    $subscription = MemberSubscription::factory()->create([
        'status_class' => ActiveMemberSubscriptionState::class,
        'end_date' => now()->subDay(),
    ]);

    $action = new ExpireMemberSubscriptionAction;
    $action($subscription);

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => MemberSubscription::class,
        'subject_id' => $subscription->id,
        'event' => 'expired',
    ]);
});

// --- Insurance expiration ---

it('expires an active insurance past its end date', function () {
    $insurance = Insurance::factory()->create([
        'status_class' => ActiveInsuranceState::class,
        'end_date' => now()->subDay(),
    ]);

    $action = new ExpireInsuranceAction;
    $action($insurance);

    $insurance->refresh();
    expect($insurance->status_class)->toBe(ExpiredInsuranceState::class);
});

it('throws exception when trying to expire a non-active insurance', function () {
    $insurance = Insurance::factory()->create([
        'status_class' => InactiveInsuranceState::class,
        'end_date' => now()->subDay(),
    ]);

    $action = new ExpireInsuranceAction;
    $action($insurance);
})->throws(\Exception::class, 'Insurance must be in Active state to expire');

it('does not expire active insurances that have not reached their end date via command', function () {
    Insurance::factory()->create([
        'status_class' => ActiveInsuranceState::class,
        'end_date' => now()->addMonth(),
    ]);

    artisan('command:ExpireInsurances')->assertSuccessful();

    expect(Insurance::where('status_class', ActiveInsuranceState::class)->count())->toBe(1);
    expect(Insurance::where('status_class', ExpiredInsuranceState::class)->count())->toBe(0);
});

it('expires only active insurances past their end date via command', function () {
    // Should be expired
    Insurance::factory()->create([
        'status_class' => ActiveInsuranceState::class,
        'end_date' => now()->subDays(10),
    ]);

    // Should NOT be expired (still active, future date)
    Insurance::factory()->create([
        'status_class' => ActiveInsuranceState::class,
        'end_date' => now()->addMonth(),
    ]);

    // Should NOT be expired (inactive state)
    Insurance::factory()->create([
        'status_class' => InactiveInsuranceState::class,
        'end_date' => now()->subDays(10),
    ]);

    artisan('command:ExpireInsurances')->assertSuccessful();

    expect(Insurance::where('status_class', ExpiredInsuranceState::class)->count())->toBe(1);
    expect(Insurance::where('status_class', ActiveInsuranceState::class)->count())->toBe(1);
    expect(Insurance::where('status_class', InactiveInsuranceState::class)->count())->toBe(1);
});

it('logs activity when insurance is expired', function () {
    $insurance = Insurance::factory()->create([
        'status_class' => ActiveInsuranceState::class,
        'end_date' => now()->subDay(),
    ]);

    $action = new ExpireInsuranceAction;
    $action($insurance);

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => Insurance::class,
        'subject_id' => $insurance->id,
        'event' => 'expired',
    ]);
});
