<?php

use App\Events\ActivateAfterPayment;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Memberships\Models\Membership;

it('ensures unique models are returned by ActivateAfterPayment', function () {
    // Create a document
    $document = Document::factory()->create();

    // Create a membership
    $membership = Membership::factory()->create();

    // Create multiple DocumentDetail entries referencing the same Membership
    DocumentDetail::factory()->count(6)->create([
        'document_id' => $document->id,
        'owner_id' => $membership->id,
        'owner_type' => Membership::class,
    ]);

    // Trigger the ActivateAfterPayment event
    $event = new ActivateAfterPayment($document->id);
    $eventModels = $event->models;

    // Assertions
    expect($eventModels)->toHaveKey(Membership::class);
    expect($eventModels[Membership::class])->toHaveCount(1);
    expect($eventModels[Membership::class][0]->id)->toBe($membership->id);
});
