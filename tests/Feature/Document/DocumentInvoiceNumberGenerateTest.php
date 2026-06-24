<?php

use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('ensures a paid document always generates an invoice number', function () {
    // Setup your environment
    $group = Group::factory()->create(['code' => 'ADMIN']);
    $admin = User::factory()->for($group, 'group')->create();
    $admin->givePermissionTo('create payment documents');
    $documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    $paymentMethod = PaymentMethod::factory()->create(['handler' => \Domain\Payments\Handlers\OfflinePaymentHandler::class]);

    // Create a document with a status that is not paid and without an invoice number
    $document = Document::factory()->create([
        'type_id' => $documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
        'invoice_number' => null, // Make sure there's no invoice number initially
    ]);

    // Simulate logging in as an admin
    $this->actingAs($admin);

    // The total amount to pay to change the status to paid
    $totalPaymentAmount = 100.00; // Equal to the total value of the document

    // Make a POST request to the route handling payments, to pay in full
    $response = $this->post(route('admin.document.manual-payment', $document), [
        'amount' => $totalPaymentAmount,
        'comment' => 'Full payment received',
    ]);

    // Assert the response is a redirect to the document's detail page with a success message
    $response->assertRedirect(route('admin.document.show', $document->id));
    $response->assertSessionHas('success', __('Document payment was saved.'));

    // Fetch the updated document and assert its new status and existence of an invoice number
    $updatedDocument = Document::find($document->id);

    expect($updatedDocument->status_class)->toEqual(PaidDocumentState::class);
    expect($updatedDocument->invoice_number)->not->toBeNull();

    // Optionally, if you want to check the format or other specific aspects of the invoice number,
    // you can add more expectations here. For example:
    // expect($updatedDocument->invoice_number)->toMatch('/^[0-9]{10}$/');

    // Additionally, check if a new payment transaction record was created
    $transaction = PaymentTransaction::where('document_id', $document->id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->amount)->toEqual($totalPaymentAmount);
    expect($transaction->status)->toEqual('success');
});
