<?php

use App\Events\DocumentMarkedAsPaid;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configure EasyPay gateway
    config([
        'payment.gateways.easypay.account_id' => 'test-account',
        'payment.gateways.easypay.api_key' => 'test-api-key',
        'payment.gateways.easypay.sandbox' => true,
    ]);

    $this->documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    DocumentType::factory()->create(['code' => 'PAY', 'name' => 'Payment']);

    $this->paymentMethod = PaymentMethod::factory()->create([
        'name' => 'EasyPay',
        'driver' => 'easypay',
        'handler' => \Domain\Payments\Handlers\EasyPayPaymentHandler::class,
        'is_enabled' => true,
    ]);
});

/**
 * EasyPay Generic Notification webhook tests.
 *
 * EasyPay does NOT use webhook signatures. Security is achieved by
 * querying their API to verify notifications are genuine.
 *
 * Generic Notification payload:
 * {
 *   "id": "payment-uuid",
 *   "key": "merchant-key",
 *   "type": "capture",
 *   "status": "success",
 *   "messages": ["..."],
 *   "date": "2022-08-10 14:56:54"
 * }
 *
 * @see https://docs.easypay.pt/docs/guides/webhooks
 */
it('marks document as paid on successful webhook', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-123',
    ]);

    // Mock EasyPay API verification call (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-123' => Http::response([
            'id' => 'test-link-123',
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
                'capture' => ['key' => $transaction->id],
            ],
        ], 200),
    ]);

    // EasyPay Generic Notification payload
    $payload = [
        'id' => 'test-link-123',
        'key' => 'merchant-key',
        'type' => 'capture',
        'status' => 'success',
        'messages' => ['Your request was successfully captured'],
        'date' => '2024-01-22 20:10:30',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $document->refresh();
    $transaction->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class)
        ->and($transaction->status)->toBe('success');

    // Verify DocumentMarkedAsPaid event was dispatched
    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document, $transaction) {
        return $event->document->id === $document->id
            && $event->transaction->id === $transaction->id
            && $event->source === 'webhook'
            && $event->createMoloniInvoice === true;
    });
});

it('handles failed payment webhook correctly', function () {
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-failed',
    ]);

    // Mock EasyPay API verification - returns expired status (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-failed' => Http::response([
            'id' => 'test-link-failed',
            'status' => 'EXPIRED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-failed',
        'type' => 'capture',
        'status' => 'failed',
        'date' => '2024-01-22 20:10:30',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'failed']);

    $document->refresh();
    $transaction->refresh();

    expect($document->status_class)->toBe(PendingDocumentState::class)
        ->and($transaction->status)->toBe('failed');
});

it('accepts webhook without signature (EasyPay does not use signatures)', function () {
    // EasyPay doesn't use signatures - they recommend API verification instead
    // This test confirms webhooks are accepted without any signature header

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-no-sig',
    ]);

    // Mock API verification (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-no-sig' => Http::response([
            'id' => 'test-link-no-sig',
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    // Send webhook without any signature header
    $response = $this->postJson('/api/payment/webhook/easypay', [
        'id' => 'test-link-no-sig',
        'type' => 'capture',
        'status' => 'success',
    ]);

    // Should be accepted (no 401)
    $response->assertStatus(200);
});

it('returns error for missing payment ID in webhook', function () {
    $payload = [
        'type' => 'capture',
        'status' => 'success',
        // Missing 'id' field
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'failed']);
});

it('handles cancelled payment webhook correctly', function () {
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-cancelled',
    ]);

    // Mock API verification - returns disabled status (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-cancelled' => Http::response([
            'id' => 'test-link-cancelled',
            'status' => 'DISABLED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-cancelled',
        'type' => 'capture',
        'status' => 'failed',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'failed']);

    $transaction->refresh();
    expect($transaction->status)->toBe('failed');
});

it('handles unknown transaction gracefully', function () {
    // Mock API that returns 404 (payment not found)
    Http::fake([
        'api.test.easypay.pt/2.0/link/non-existent' => Http::response([], 404),
        'api.test.easypay.pt/2.0/single/non-existent' => Http::response([], 404),
    ]);

    $payload = [
        'id' => 'non-existent',
        'type' => 'capture',
        'status' => 'success',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    // Should return failed status (transaction not found)
    $response->assertStatus(200)
        ->assertJson(['status' => 'failed']);
});

it('handles duplicate webhook with idempotency - transaction already success', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    // Transaction already marked as success
    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'success',
        'comment' => 'EasyPay Link: test-link-duplicate',
    ]);

    // Mock API verification (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-duplicate' => Http::response([
            'id' => 'test-link-duplicate',
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-duplicate',
        'type' => 'capture',
        'status' => 'success',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'already_processed']);

    // Event should NOT be dispatched for duplicate
    Event::assertNotDispatched(DocumentMarkedAsPaid::class);
});

it('handles duplicate webhook with idempotency - document already paid', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PaidDocumentState::class, // Already paid
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending', // Transaction not yet marked success
        'comment' => 'EasyPay Link: test-link-doc-paid',
    ]);

    // Mock API verification (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-doc-paid' => Http::response([
            'id' => 'test-link-doc-paid',
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-doc-paid',
        'type' => 'capture',
        'status' => 'success',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'already_processed']);

    // Transaction should be updated to success even though document was already paid
    $transaction->refresh();
    expect($transaction->status)->toBe('success');

    // Event should NOT be dispatched for already-paid document
    Event::assertNotDispatched(DocumentMarkedAsPaid::class);
});

it('dispatches DocumentMarkedAsPaid event with correct data', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 150.00,
    ]);

    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 150.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-event',
    ]);

    // Mock API verification with full payment details (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-event' => Http::response([
            'id' => 'test-link-event',
            'status' => 'FINALIZED',
            'payment' => [
                'methods' => ['CC'],
                'single' => ['requested_amount' => '150.00'],
                'capture' => ['key' => $transaction->id],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-event',
        'type' => 'capture',
        'status' => 'success',
        'date' => '2024-01-22 20:10:30',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    Event::assertDispatched(DocumentMarkedAsPaid::class, function ($event) use ($document, $transaction) {
        return $event->document->id === $document->id
            && $event->transaction->id === $transaction->id
            && $event->source === 'webhook'
            && $event->createMoloniInvoice === true
            && $event->getAmount() === 150.00;
    });
});

it('does not dispatch event for failed payment', function () {
    Event::fake([DocumentMarkedAsPaid::class]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-no-event',
    ]);

    // Mock API verification - returns expired (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-no-event' => Http::response([
            'id' => 'test-link-no-event',
            'status' => 'EXPIRED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-no-event',
        'type' => 'capture',
        'status' => 'failed',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200);

    Event::assertNotDispatched(DocumentMarkedAsPaid::class);
});

it('verifies payment by querying EasyPay API', function () {
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-link-api-verify',
    ]);

    // Mock API verification (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-link-api-verify' => Http::response([
            'id' => 'test-link-api-verify',
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-link-api-verify',
        'type' => 'capture',
        'status' => 'success',
    ];

    $this->postJson('/api/payment/webhook/easypay', $payload);

    // Verify API was called to verify the payment
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.test.easypay.pt/2.0/link/test-link-api-verify'
            && $request->method() === 'GET';
    });
});

it('finds transaction by merchant key when webhook ID differs from link ID', function () {
    // This tests the real-world scenario where EasyPay sends a different ID
    // in the webhook than the link ID we stored when creating the payment.
    // The 'key' field in the webhook contains our transaction ID.

    Event::fake([DocumentMarkedAsPaid::class]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    // Create transaction - note the link ID stored in comment
    $transaction = PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: original-link-id-123',
    ]);

    // Webhook sends a DIFFERENT ID, but includes our transaction ID in the 'key' field
    $webhookPaymentId = 'different-payment-page-id-456';

    // Mock API verification using the webhook's payment ID (API v2.0 format)
    Http::fake([
        "api.test.easypay.pt/2.0/link/{$webhookPaymentId}" => Http::response([
            'id' => $webhookPaymentId,
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
                'capture' => ['key' => $transaction->id],
            ],
        ], 200),
    ]);

    // EasyPay webhook with different ID but our transaction ID in 'key'
    $payload = [
        'id' => $webhookPaymentId, // Different from link ID
        'key' => $transaction->id, // Our transaction ID
        'type' => 'capture',
        'status' => 'success',
        'messages' => ['Your request was successfully captured'],
        'date' => '2024-01-22 20:10:30',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $document->refresh();
    $transaction->refresh();

    // Verify payment was processed correctly
    expect($document->status_class)->toBe(PaidDocumentState::class)
        ->and($transaction->status)->toBe('success');

    Event::assertDispatched(DocumentMarkedAsPaid::class);
});

it('falls back to single endpoint if link endpoint fails', function () {
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    PaymentTransaction::create([
        'document_id' => $document->id,
        'payment_method_id' => $this->paymentMethod->id,
        'amount' => 100.00,
        'status' => 'pending',
        'comment' => 'EasyPay Link: test-single-123',
    ]);

    // Mock API - link endpoint returns 404, single endpoint works (API v2.0 format)
    Http::fake([
        'api.test.easypay.pt/2.0/link/test-single-123' => Http::response([], 404),
        'api.test.easypay.pt/2.0/single/test-single-123' => Http::response([
            'id' => 'test-single-123',
            'status' => 'FINALIZED',
            'payment' => [
                'single' => ['requested_amount' => '100.00'],
            ],
        ], 200),
    ]);

    $payload = [
        'id' => 'test-single-123',
        'type' => 'capture',
        'status' => 'success',
    ];

    $response = $this->postJson('/api/payment/webhook/easypay', $payload);

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    // Verify both endpoints were tried
    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/link/test-single-123');
    });
    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/single/test-single-123');
    });
});
