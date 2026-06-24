<?php

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PendingDocumentState;
use Domain\Payments\Gateways\EasyPayGateway;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->documentType = DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    DocumentType::factory()->create(['code' => 'PAY', 'name' => 'Payment']);

    $this->paymentMethod = PaymentMethod::factory()->create([
        'name' => 'EasyPay',
        'driver' => 'easypay',
        'handler' => \Domain\Payments\Handlers\EasyPayPaymentHandler::class,
        'is_enabled' => true,
    ]);
});

it('creates payment link and returns redirect PaymentResponseData', function () {
    Http::fake([
        'api.test.easypay.pt/*' => Http::response([
            'id' => 'test-link-123',
            'url' => 'https://shortener.test.easypay.pt/abc123',
            'status' => 'ACTIVE',
            'image' => 'https://cdn.sandbox.easypay.pt/images/qr/abc123.jpeg',
        ], 201),
    ]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'test-account',
        'api_key' => 'test-key',
        'sandbox' => true,
    ]);

    $response = $gateway->createPayment($document);

    expect($response->requiresRedirect())->toBeTrue()
        ->and($response->redirectUrl)->toBe('https://shortener.test.easypay.pt/abc123')
        ->and($response->gatewayReference)->toBe('test-link-123');

    // Verify transaction was created
    $transaction = PaymentTransaction::where('document_id', $document->id)->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe('pending')
        ->and($transaction->amount)->toBe(100.00)
        ->and($transaction->comment)->toContain('EasyPay Link: test-link-123');
});

it('sends correct payment data to EasyPay API', function () {
    Http::fake([
        'api.test.easypay.pt/*' => Http::response([
            'id' => 'test-link-456',
            'url' => 'https://shortener.test.easypay.pt/def456',
            'status' => 'ACTIVE',
        ], 201),
    ]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 50.00,
    ]);

    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'test-account',
        'api_key' => 'test-key',
        'sandbox' => true,
    ]);

    $gateway->createPayment($document);

    // Verify API was called with correct Pay By Link structure (API v2.0 - January 2026)
    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === 'https://api.test.easypay.pt/2.0/link'
            && $body['type'] === 'SINGLE'
            && isset($body['customer']['name'])
            && isset($body['customer']['email'])
            && isset($body['customer']['phone'])
            && isset($body['customer']['language'])
            && isset($body['communication_channels'])
            && isset($body['payment']['methods'])
            && isset($body['payment']['single']['requested_amount'])
            && $body['payment']['single']['requested_amount'] === '50.00'
            // CRITICAL: 'key' must be set in payment.capture for webhook correlation
            && isset($body['payment']['capture']['key'])
            && ! empty($body['payment']['capture']['key']);
    });
});

it('returns failed response when API returns error', function () {
    Http::fake([
        'api.test.easypay.pt/*' => Http::response([
            'message' => 'Invalid API credentials',
        ], 401),
    ]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'invalid-account',
        'api_key' => 'invalid-key',
        'sandbox' => true,
    ]);

    $response = $gateway->createPayment($document);

    expect($response->isFailed())->toBeTrue()
        ->and($response->errorMessage)->toContain('Invalid API credentials');
});

it('returns failed response when API response is missing required fields', function () {
    Http::fake([
        'api.test.easypay.pt/*' => Http::response([
            // Missing 'id' and 'url' fields
            'status' => 'ACTIVE',
        ], 201),
    ]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'test-account',
        'api_key' => 'test-key',
        'sandbox' => true,
    ]);

    $response = $gateway->createPayment($document);

    expect($response->isFailed())->toBeTrue()
        ->and($response->errorMessage)->toContain('missing link id or url');
});

it('throws exception when required config is missing', function () {
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $gateway = new EasyPayGateway;
    $gateway->configure([
        'sandbox' => true,
        // Missing account_id and api_key
    ]);

    expect(fn () => $gateway->createPayment($document))
        ->toThrow(\InvalidArgumentException::class, 'Missing required configuration key');
});

/**
 * EasyPay does NOT use webhook signatures for authentication.
 * Their security model relies on querying their API to verify notifications.
 * The validateWebhookSignature method always returns true.
 *
 * @see https://docs.easypay.pt/docs/guides/webhooks
 */
it('always accepts webhook (EasyPay does not use signatures)', function () {
    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'test-account',
        'api_key' => 'test-key',
    ]);

    $payload = '{"id":"test-link","type":"capture","status":"success"}';

    // EasyPay doesn't use signatures, so validation always passes
    // Security is achieved by querying their API in verifyPayment()
    $isValid = $gateway->validateWebhookSignature([], $payload);

    expect($isValid)->toBeTrue();
});

it('accepts webhook with any headers (EasyPay does not use signatures)', function () {
    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'test-account',
        'api_key' => 'test-key',
    ]);

    $payload = '{"id":"test-link","type":"capture","status":"success"}';

    // Even with random headers, webhook is accepted
    // Real security happens via API verification in verifyPayment()
    $isValid = $gateway->validateWebhookSignature(
        ['X-Some-Header' => 'some-value'],
        $payload
    );

    expect($isValid)->toBeTrue();
});

it('uses production URL when sandbox is disabled', function () {
    Http::fake([
        'api.prod.easypay.pt/*' => Http::response([
            'id' => 'prod-link-123',
            'url' => 'https://shortener.easypay.pt/prod123',
            'status' => 'ACTIVE',
        ], 201),
    ]);

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'method_id' => $this->paymentMethod->id,
        'owner_type' => 'individual',
        'total_value' => 100.00,
    ]);

    $gateway = new EasyPayGateway;
    $gateway->configure([
        'account_id' => 'prod-account',
        'api_key' => 'prod-key',
        'sandbox' => false,
    ]);

    $response = $gateway->createPayment($document);

    expect($response->requiresRedirect())->toBeTrue()
        ->and($response->redirectUrl)->toBe('https://shortener.easypay.pt/prod123');

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://api.prod.easypay.pt');
    });
});
