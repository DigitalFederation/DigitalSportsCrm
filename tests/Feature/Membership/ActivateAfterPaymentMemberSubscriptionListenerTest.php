<?php

use App\Models\User;
use Carbon\Carbon;
use Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\PendingPaymentInsuranceState;
use Domain\Memberships\Actions\ActivateMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create required document type and payment method
    DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    DocumentType::factory()->create(['code' => 'INV', 'name' => 'Invoice']);
    DocumentType::factory()->create(['code' => 'PAY', 'name' => 'Payment']);
    PaymentMethod::factory()->create([
        'handler' => \Domain\Payments\Handlers\OfflinePaymentHandler::class,
    ]);
});

it('activates member subscription after document payment for individual', function () {
    // Create individual with user
    $user = User::factory()->create();
    $individual = Individual::factory()->create(['user_id' => $user->id]);

    // Create membership package
    $membershipPackage = MembershipPackage::create([
        'name' => 'Premium Package',
        'price' => 199.99,
        'description' => 'Premium membership package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create member subscription in pending payment state
    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create document with member subscription as owner
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => Individual::class,
        'owner_id' => $individual->id,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription->id,
        'owner_type' => MemberSubscription::class,
        'description' => 'Premium Package Subscription',
    ]);

    // Mock notifications
    Notification::fake();

    // Mark document as paid
    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    // Refresh models
    $document->refresh();
    $memberSubscription->refresh();

    // Assert document is paid
    expect($document->status_class)->toBe(PaidDocumentState::class);

    // Assert member subscription is activated
    expect($memberSubscription->status_class)->toBe(ActiveMemberSubscriptionState::class);

    // Assert notification was sent to user
    Notification::assertSentTo($user, \App\Notifications\UserAlert::class);
});

it('activates member subscription after document payment for entity', function () {
    // Create entity with users
    $entity = Entity::factory()->create();
    $users = User::factory()->count(3)->create();
    $entity->users()->attach($users->pluck('id'));

    // Create membership package
    $membershipPackage = MembershipPackage::create([
        'name' => 'Enterprise Package',
        'price' => 999.99,
        'description' => 'Enterprise membership package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create member subscription for entity
    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Entity::class,
        'member_id' => $entity->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create document
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => Entity::class,
        'owner_id' => $entity->id,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription->id,
        'owner_type' => MemberSubscription::class,
    ]);

    // Mock notifications
    Notification::fake();

    // Mark document as paid
    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    // Refresh models
    $memberSubscription->refresh();

    // Assert member subscription is activated
    expect($memberSubscription->status_class)->toBe(ActiveMemberSubscriptionState::class);

    // Assert all entity users received notifications
    Notification::assertSentTo($users, \App\Notifications\UserAlert::class);
});

it('does not activate member subscription if already active', function () {
    $individual = Individual::factory()->create();
    $membershipPackage = MembershipPackage::create([
        'name' => 'Test Package',
        'price' => 99.99,
        'description' => 'Test package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create already active member subscription
    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => ActiveMemberSubscriptionState::class,
    ]);

    $action = new ActivateMemberSubscriptionAction;

    // This should not throw an exception but log a warning
    expect(fn () => $action($memberSubscription->id))->not()->toThrow(\Exception::class);

    // Subscription should remain active
    $memberSubscription->refresh();
    expect($memberSubscription->status_class)->toBe(ActiveMemberSubscriptionState::class);
});

it('handles multiple member subscriptions in same document', function () {
    $individual1 = Individual::factory()->create();
    $individual2 = Individual::factory()->create();
    $membershipPackage = MembershipPackage::create([
        'name' => 'Test Package',
        'price' => 99.99,
        'description' => 'Test package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create two member subscriptions
    $subscription1 = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual1->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    $subscription2 = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual2->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create document with both subscriptions
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $subscription1->id,
        'owner_type' => MemberSubscription::class,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $subscription2->id,
        'owner_type' => MemberSubscription::class,
    ]);

    // Mark document as paid
    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    // Both subscriptions should be activated
    $subscription1->refresh();
    $subscription2->refresh();

    expect($subscription1->status_class)->toBe(ActiveMemberSubscriptionState::class);
    expect($subscription2->status_class)->toBe(ActiveMemberSubscriptionState::class);
});

it('handles errors gracefully when activating member subscription', function () {
    $individual = Individual::factory()->create();
    $membershipPackage = MembershipPackage::create([
        'name' => 'Test Package',
        'price' => 99.99,
        'description' => 'Test package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription->id,
        'owner_type' => MemberSubscription::class,
    ]);

    // Delete the subscription to cause an error
    $memberSubscription->delete();

    // The listener should handle the error gracefully
    expect(fn () => (new ManuallyMarkDocumentAsPaidAction)->execute($document->id))
        ->not()->toThrow(\Exception::class);

    // Document should still be marked as paid
    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);
});

it('activates member subscription with mixed model types in document details', function () {
    $individual = Individual::factory()->create();
    $individual2 = Individual::factory()->create();
    $membershipPackage = MembershipPackage::create([
        'name' => 'Test Package',
        'price' => 99.99,
        'description' => 'Test package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create member subscription
    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create another member subscription to test mixed types
    $memberSubscription2 = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual2->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create document with multiple types of document details
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    // Add member subscription to document
    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription->id,
        'owner_type' => MemberSubscription::class,
    ]);

    // Add another member subscription
    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription2->id,
        'owner_type' => MemberSubscription::class,
    ]);

    // Add a manual service entry (no owner)
    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => null,
        'owner_type' => null,
        'description' => 'Manual service fee',
        'total_value' => 50.00,
    ]);

    // Mark document as paid
    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    // Both member subscriptions should be activated
    $memberSubscription->refresh();
    $memberSubscription2->refresh();
    expect($memberSubscription->status_class)->toBe(ActiveMemberSubscriptionState::class);
    expect($memberSubscription2->status_class)->toBe(ActiveMemberSubscriptionState::class);
});

it('activates related insurances when member subscription is activated after payment', function () {
    $user = User::factory()->create();
    $individual = Individual::factory()->create(['user_id' => $user->id]);

    // Create membership package
    $membershipPackage = MembershipPackage::create([
        'name' => 'Insurance Package',
        'price' => 50.00,
        'description' => 'Insurance only package',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create insurance plan
    $insurancePlan = InsurancePlan::factory()->create([
        'name' => 'Diving Insurance',
        'individual_fee' => 50.00,
    ]);

    // Create member subscription in pending payment state
    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create insurance linked to the subscription in pending payment state
    $insurance = Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'member_subscription_id' => $memberSubscription->id,
        'status_class' => PendingPaymentInsuranceState::class,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
    ]);

    // Create document with member subscription as owner
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => Individual::class,
        'owner_id' => $individual->id,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription->id,
        'owner_type' => MemberSubscription::class,
        'description' => 'Insurance Package Subscription',
    ]);

    // Mock notifications
    Notification::fake();

    // Mark document as paid
    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    // Refresh models
    $memberSubscription->refresh();
    $insurance->refresh();

    // Assert member subscription is activated
    expect($memberSubscription->status_class)->toBe(ActiveMemberSubscriptionState::class);

    // Assert insurance is activated
    expect($insurance->status_class)->toBe(ActiveInsuranceState::class);
});

it('activates multiple insurances when member subscription is activated after payment', function () {
    $user = User::factory()->create();
    $individual = Individual::factory()->create(['user_id' => $user->id]);

    // Create membership package
    $membershipPackage = MembershipPackage::create([
        'name' => 'Multi-Insurance Package',
        'price' => 100.00,
        'description' => 'Package with multiple insurances',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    // Create insurance plans
    $insurancePlan1 = InsurancePlan::factory()->create(['name' => 'Diving Insurance']);
    $insurancePlan2 = InsurancePlan::factory()->create(['name' => 'Pool Insurance']);

    // Create member subscription
    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create multiple insurances linked to the subscription
    $insurance1 = Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan1->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'member_subscription_id' => $memberSubscription->id,
        'status_class' => PendingPaymentInsuranceState::class,
    ]);

    $insurance2 = Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan2->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'member_subscription_id' => $memberSubscription->id,
        'status_class' => PendingPaymentInsuranceState::class,
    ]);

    // Create document
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => Individual::class,
        'owner_id' => $individual->id,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $memberSubscription->id,
        'owner_type' => MemberSubscription::class,
    ]);

    // Mock notifications
    Notification::fake();

    // Mark document as paid
    (new ManuallyMarkDocumentAsPaidAction)->execute($document->id);

    // Refresh models
    $memberSubscription->refresh();
    $insurance1->refresh();
    $insurance2->refresh();

    // Assert subscription is activated
    expect($memberSubscription->status_class)->toBe(ActiveMemberSubscriptionState::class);

    // Assert both insurances are activated
    expect($insurance1->status_class)->toBe(ActiveInsuranceState::class);
    expect($insurance2->status_class)->toBe(ActiveInsuranceState::class);
});

it('does not re-activate already active insurances', function () {
    $individual = Individual::factory()->create();

    $membershipPackage = MembershipPackage::create([
        'name' => 'Test Package',
        'price' => 50.00,
        'description' => 'Test',
        'vat_rate' => 23,
        'is_active' => true,
    ]);

    $insurancePlan = InsurancePlan::factory()->create();

    $memberSubscription = MemberSubscription::create([
        'membership_package_id' => $membershipPackage->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'start_date' => Carbon::now(),
        'end_date' => Carbon::now()->addYear(),
        'status_class' => PendingPaymentMemberSubscriptionState::class,
    ]);

    // Create insurance that is already active
    $insurance = Insurance::factory()->create([
        'insurance_plan_id' => $insurancePlan->id,
        'member_type' => Individual::class,
        'member_id' => $individual->id,
        'member_subscription_id' => $memberSubscription->id,
        'status_class' => ActiveInsuranceState::class,
    ]);

    $originalUpdatedAt = $insurance->updated_at;

    $action = new ActivateMemberSubscriptionAction;
    $action($memberSubscription->id);

    $insurance->refresh();

    // Insurance should remain active
    expect($insurance->status_class)->toBe(ActiveInsuranceState::class);
});
