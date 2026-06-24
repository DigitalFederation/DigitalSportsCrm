<?php

use App\Models\Committee;
use Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\PendingMembershipState;
use Domain\Payments\Models\PaymentMethod;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('handles document payment, membership activation, and invoice number generation', function () {
    // Step 1: Set up the necessary models
    $federation = Federation::factory()->create(['is_local' => false]);
    $committee = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Technical Committee']);

    PaymentMethod::factory()->create([
        'handler' => \Domain\Payments\Handlers\OfflinePaymentHandler::class,
    ]);
    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'type_id' => $documentType->id,
    ]);

    $membership = Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => PendingMembershipState::class,
    ]);
    $membershipPlan = MembershipPlan::factory()->create(['committee_id' => $committee->id]);
    $membership->plans()->attach($membershipPlan->id);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $membership->id,
        'owner_type' => Membership::class,
    ]);

    // Step 2: Mark the document as paid (triggering the entire flow)
    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    // Refresh models to get updated data
    $document->refresh();
    $membership->refresh();

    // Step 3: Assertions
    // Check if document status is updated and invoice number is generated
    expect($document->status_class)->toBe(PaidDocumentState::class); // Replace with actual paid state class
    expect($document->invoice_number)->not()->toBeNull();

    // Check if membership is activated
    expect($membership->status_class)->toBe(ActiveMembershipState::class);

    // Optionally, you can also assert that the ActivateAfterPayment event was dispatched
    // and the MembershipActivationNotification was sent, depending on how your system is set up.
});
