<?php

use App\Events\DocumentMarkedAsPaid;
use App\Models\Group;
use App\Models\User;
use Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
use Domain\Documents\Actions\RegisterDocumentPaymentAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PartiallyPaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('registers a partial payment for a document and updates its status', function () {
    // Setup your environment
    $group = Group::factory()->create(['code' => 'ADMIN']);
    $admin = User::factory()->for($group, 'group')->create();

    $admin->givePermissionTo('create payment documents');
    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    $paymentMethod = PaymentMethod::factory()->create(['handler' => \Domain\Payments\Handlers\OfflinePaymentHandler::class]);
    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00, // Assume total value needed for the document
    ]);

    // Simulate logging in as an admin
    $this->actingAs($admin);

    // Amount to pay partially
    $partialPaymentAmount = 50; // 50% of the total value

    // Make a POST request to the route handling partial payments
    $response = $this->post(route('admin.document.manual-payment', $document), [
        'amount' => $partialPaymentAmount,
        'comment' => 'Partial payment received',
    ]);

    // Assert the response is a redirect to the document's detail page with success message
    $response->assertRedirect(route('admin.document.show', $document->id));
    $response->assertSessionHas('success', __('Document payment was saved.'));

    // Fetch the updated document and assert its status
    $updatedDocument = Document::find($document->id);

    expect($updatedDocument->status_class)->toEqual(PartiallyPaidDocumentState::class);
    expect($updatedDocument->amount_paid)->toEqual($partialPaymentAmount);

    // Additionally, check if a new payment transaction record was created
    $transaction = PaymentTransaction::where('document_id', $document->id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->amount)->toEqual($partialPaymentAmount);
    expect($transaction->status)->toEqual('success');

});

it('marks document as fully paid and dispatches DocumentMarkedAsPaid event without Moloni invoice', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
    ]);

    $action = new ManuallyMarkDocumentAsPaidAction;
    $action->execute($document->id, 'Manual payment test', false);

    $document->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->source === 'manual'
            && $event->createMoloniInvoice === false
            && $event->transaction !== null;
    });
});

it('marks document as fully paid and dispatches DocumentMarkedAsPaid event with Moloni invoice', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 150.00,
    ]);

    $action = new ManuallyMarkDocumentAsPaidAction;
    $action->execute($document->id, 'Manual payment with invoice', true);

    $document->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->source === 'manual'
            && $event->createMoloniInvoice === true
            && $event->transaction !== null
            && $event->getAmount() === 150.00;
    });
});

it('creates payment transaction when marking document as fully paid', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 200.00,
    ]);

    $action = new ManuallyMarkDocumentAsPaidAction;
    $action->execute($document->id, 'Test comment', false);

    $transaction = PaymentTransaction::where('document_id', $document->id)->first();

    expect($transaction)->not->toBeNull()
        ->and((float) $transaction->amount)->toBe(200.00)
        ->and($transaction->status)->toBe('success')
        ->and($transaction->comment)->toBe('Test comment');
});

it('passes correct transaction to DocumentMarkedAsPaid event', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 75.50,
    ]);

    $action = new ManuallyMarkDocumentAsPaidAction;
    $action->execute($document->id, 'Transaction test', true);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->transaction !== null
            && (float) $event->transaction->amount === 75.50
            && $event->transaction->status === 'success';
    });
});

// Tests for RegisterDocumentPaymentAction (used by the actual form/controller)

it('RegisterDocumentPaymentAction dispatches DocumentMarkedAsPaid when payment completes document', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
        'amount_paid' => 0,
    ]);

    $action = new RegisterDocumentPaymentAction;
    $action->execute($document->id, 100.00, 'Full payment', true);

    $document->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->source === 'manual'
            && $event->createMoloniInvoice === true;
    });
});

it('RegisterDocumentPaymentAction does not dispatch event for partial payment', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
        'amount_paid' => 0,
    ]);

    $action = new RegisterDocumentPaymentAction;
    $action->execute($document->id, 50.00, 'Partial payment', true);

    $document->refresh();

    expect($document->status_class)->toBe(PartiallyPaidDocumentState::class);

    // Event should NOT be dispatched for partial payment
    Event::assertNotDispatched(DocumentMarkedAsPaid::class);
});

it('RegisterDocumentPaymentAction respects createMoloniInvoice flag', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
        'amount_paid' => 0,
    ]);

    $action = new RegisterDocumentPaymentAction;
    $action->execute($document->id, 100.00, 'Full payment without invoice', false);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->createMoloniInvoice === false;
    });
});

it('RegisterDocumentPaymentAction dispatches event when completing previously partial payment', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    PaymentMethod::factory()->create(['handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler']);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PartiallyPaidDocumentState::class,
        'total_value' => 100.00,
        'amount_paid' => 50.00, // Already partially paid
    ]);

    $action = new RegisterDocumentPaymentAction;
    $action->execute($document->id, 50.00, 'Completing payment', true);

    $document->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document) {
        return $event->document->id === $document->id
            && $event->source === 'manual'
            && $event->createMoloniInvoice === true;
    });
});
