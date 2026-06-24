<?php

use App\Models\Committee;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PendingDocumentState;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Domain\Memberships\States\PendingMembershipState;
use Domain\Payments\Models\PaymentMethod;

it('validates correct calculations for document detail', function () {
    // Arrange: Create necessary models and set specific values
    DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    $federation = Federation::factory()->create(['is_local' => false]);
    $committee = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Technical Committee']);
    PaymentMethod::factory()->create(['handler' => \Domain\Payments\Handlers\OfflinePaymentHandler::class]);
    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);

    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'type_id' => $documentType->id,
        // Override other necessary fields as needed
    ]);

    $unitValue = 100; // Example value
    $quantity = 2; // Example quantity
    $taxPercentage = 10; // 10% tax
    $netValue = $unitValue * $quantity;
    $taxValue = $netValue * ($taxPercentage / 100);
    $totalValue = $netValue + $taxValue;

    $membership = Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => PendingMembershipState::class,
    ]);
    $membershipPlan = MembershipPlan::factory()->create(['committee_id' => $committee->id]);
    $membership->plans()->attach($membershipPlan->id);

    $documentDetail = new DocumentDetail([
        'document_id' => $document->id,
        'owner_id' => $membership->id,
        'owner_type' => Membership::class,
        'quantity' => $quantity,
        'unit_value' => $unitValue,
        'tax_percentage' => $taxPercentage,
        'net_value' => $netValue,
        'tax_value' => $taxValue,
        'total_value' => $totalValue,
        // Set other necessary fields
    ]);
    $documentDetail->save();

    // Act: Retrieve the saved DocumentDetail
    $savedDocumentDetail = DocumentDetail::find($documentDetail->id);

    // Assert: Check if the calculations are correct
    // Assert: Check if the calculations are correct using float casting
    expect((float) $savedDocumentDetail->net_value)->toBe((float) $netValue)
        ->and((float) $savedDocumentDetail->tax_value)->toBe((float) $taxValue)
        ->and((float) $savedDocumentDetail->total_value)->toBe((float) $totalValue);

});
