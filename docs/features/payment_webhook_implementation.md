# Payment Webhook Implementation

> **Bundled Portugal-specific references.** This describes the webhook flow for the bundled EasyPay
> payment gateway and Moloni invoicing provider — both **optional and disabled unless configured**.
> The architecture (idempotent webhook handling, post-payment invoice generation) is generic; EasyPay
> and Moloni are reference implementations. See [Building Integrations](/guides/building-integrations)
> to add your own.

This document describes the EasyPay payment webhook implementation, including the callback handling architecture, idempotency protection, and external invoice API integration.

---

## Table of Contents

1. [Overview](#1-overview)
2. [Architecture](#2-architecture)
3. [Webhook Flow](#3-webhook-flow)
4. [Idempotency Protection](#4-idempotency-protection)
5. [External Invoice Integration](#5-external-invoice-integration)
6. [Configuration](#6-configuration)
7. [Admin Interface](#7-admin-interface)
8. [Testing](#8-testing)
9. [Troubleshooting](#9-troubleshooting)
10. [Key Files](#10-key-files)

---

## 1. Overview

The payment webhook system handles asynchronous payment notifications from EasyPay. When a customer completes (or fails) a payment, EasyPay sends a webhook to our endpoint. The system:

- Verifies webhook authenticity by querying EasyPay API (EasyPay does NOT use signatures)
- Prevents duplicate processing through idempotency checks
- Marks documents as paid and triggers activation flows
- Dispatches events for external integrations (e.g., invoice generation APIs)

> **Note**: For full EasyPay documentation including authorization/capture flows,
> see [EasyPay Integration](/easypay_integration).

---

## 2. Architecture

### Component Diagram

```
                                    +-----------------------+
                                    |      EasyPay API      |
                                    +-----------+-----------+
                                                |
                                                | POST /api/payment/webhook/easypay
                                                v
+-----------------------------------------------------------------------------------+
|                           PaymentWebhookController                                 |
|                                                                                   |
|  1. Validate Signature (no-op for EasyPay - always passes)                        |
|  2. Verify Payment (via Gateway)                                                  |
|  3. Idempotency Checks                                                            |
|  4. Update Transaction Status (with row locking)                                  |
|  5. Mark Document as Paid (MarkAsPaidAction)                                      |
|  6. Dispatch DocumentMarkedAsPaid Event                                           |
+-----------------------------------------------------------------------------------+
                                                |
                    +---------------------------+---------------------------+
                    |                                                       |
                    v                                                       v
+-----------------------------------+               +-----------------------------------+
|     ActivateAfterPayment Event    |               |   DocumentMarkedAsPaid Event      |
|                                   |               |                                   |
|  - Activates Memberships          |               |  - DispatchInvoiceGeneration      |
|  - Activates Licenses             |               |    Listener (queued)              |
|  - Activates Certifications       |               |                                   |
|  - Activates Enrollments          |               |  - GenerateExternalInvoiceJob     |
+-----------------------------------+               +-----------------------------------+
```

### Key Components

| Component | Purpose |
|-----------|---------|
| `PaymentWebhookController` | Receives and processes EasyPay webhooks |
| `EasyPayGateway` | Verifies payments via the EasyPay API and interprets webhook data |
| `MarkAsPaidAction` | Marks documents as paid and creates payment records |
| `DocumentMarkedAsPaid` | Event dispatched after a document is marked as paid |
| `DispatchInvoiceGenerationListener` | Queues the invoice generation job |
| `GenerateExternalInvoiceJob` | Generates the external (Moloni) invoice |

> **Signature validation**: The controller calls `EasyPayGateway::validateWebhookSignature()`
> and returns `401` if it fails. For EasyPay this hook is a deliberate no-op that always
> returns `true` — EasyPay does not send signatures, so authenticity is established by
> querying the EasyPay API in `verifyPayment()` instead. The hook exists so other gateways
> can implement real signature checks.

---

## 3. Webhook Flow

### Successful Payment Flow

```
1. EasyPay sends POST to /api/payment/webhook/easypay
   └── Body (Generic Notification): { "id": "...", "type": "capture", "status": "success", ... }

2. Controller accepts request (no signature validation - EasyPay doesn't use signatures)

3. Gateway queries EasyPay API to verify notification is genuine
   └── GET /link/{id} or GET /single/{id}
   └── If API query fails: 200 { "status": "failed" }

4. Gateway finds local transaction by payment ID
   └── If not found: 200 { "status": "failed" }

5. Idempotency checks:
   └── Transaction already success? → 200 { "status": "already_processed" }
   └── Document already paid? → 200 { "status": "already_processed" }

6. Within DB transaction (with row locking):
   └── Update transaction status to 'success'
   └── Execute MarkAsPaidAction
       └── Creates payment document
       └── Changes document state to Paid
       └── Fires ActivateAfterPayment event

6. After commit:
   └── Dispatch DocumentMarkedAsPaid event
       └── DispatchInvoiceGenerationListener queues GenerateExternalInvoiceJob

7. Return 200 { "status": "success" }
```

### Failed Payment Flow

```
1. EasyPay sends webhook with status: "failed" | "cancelled" | "expired"

2. Controller accepts request (no signature validation - EasyPay doesn't use signatures)

3. Controller updates transaction status to 'failed'
   └── Idempotency: Only updates if not already 'failed'

4. Return 200 { "status": "failed" }
```

---

## 4. Idempotency Protection

The system implements multiple layers of idempotency protection to handle webhook retries:

### Layer 1: Transaction Status Check

```php
// In PaymentWebhookController::handleSuccessfulPayment()
if ($transaction->status === 'success') {
    return response()->json(['status' => 'already_processed'], 200);
}
```

### Layer 2: Document Status Check

```php
if ($document->status_class === PaidDocumentState::class) {
    $transaction->update(['status' => 'success']);
    return response()->json(['status' => 'already_processed'], 200);
}
```

### Layer 3: Row-Level Locking

```php
DB::transaction(function () use ($document, $transaction, ...) {
    // Acquire exclusive lock on transaction row
    $lockedTransaction = PaymentTransaction::lockForUpdate()->find($transaction->id);

    // Double-check after acquiring lock (handles race conditions)
    if ($lockedTransaction->status === 'success') {
        return;
    }

    // Process payment...
});
```

### Why Three Layers?

1. **Layer 1 & 2**: Fast rejection of duplicates without acquiring locks
2. **Layer 3**: Prevents race conditions when concurrent webhooks arrive

---

## 5. External Invoice Integration

The platform integrates with **Moloni** (a Portuguese invoicing service) to generate
invoice-receipts after a payment is confirmed. The integration is wired through the
`DocumentMarkedAsPaid` event, a queued listener, and a queued job.

### DocumentMarkedAsPaid Event

The `DocumentMarkedAsPaid` event is the hook point for external invoice generation:

```php
// app/Events/DocumentMarkedAsPaid.php
class DocumentMarkedAsPaid
{
    public function __construct(
        public Document $document,
        public ?PaymentTransaction $transaction,
        public bool $createMoloniInvoice = true,
        public string $source = 'webhook',
        public array $webhookData = []
    ) {}

    public function getAmount(): float;        // transaction amount, or document total
    public function isManualPayment(): bool;   // true when $source === 'manual'
}
```

It can be dispatched from two places:

- **Payment webhooks** (automatic payments) — `source: 'webhook'`, always creates an invoice.
- **Manual payment marking** (admin) — `source: 'manual'`, creates an invoice only when the admin opted in (`createMoloniInvoice`).

### DispatchInvoiceGenerationListener

The listener (`ShouldQueueAfterCommit`) queues the job only when invoice generation
should happen. Its `shouldQueue()` returns false when:

- `createMoloniInvoice` is false, or
- the document's detail owner types are not enabled for invoicing
  (`MoloniSettingsService::shouldGenerateInvoiceForDocument()`).

### GenerateExternalInvoiceJob

A queued job (implements `ShouldQueue` and `ShouldBeUnique`) that calls the real Moloni
integration. It is already fully implemented:

```php
// app/Jobs/GenerateExternalInvoiceJob.php
class GenerateExternalInvoiceJob implements ShouldBeUnique, ShouldQueue
{
    public int $tries = 3;
    public int $uniqueFor = 300;
    public array $backoff = [30, 60, 120];  // Exponential backoff
    public int $timeout = 60;

    public function handle(CreateMoloniInvoiceReceiptAction $createInvoiceAction): void
    {
        // Idempotency: skip if an invoice already exists for this document
        if (MoloniInvoice::existsForDocument($this->document->id)) {
            return;
        }

        // Create the Moloni invoice-receipt; returns a MoloniInvoice model
        // (or null when Moloni is disabled / not configured).
        $moloniInvoice = $createInvoiceAction($this->document, $this->transaction);
    }
}
```

Key points:

- **Idempotency** is enforced via `MoloniInvoice::existsForDocument()` (backed by the
  `moloni_invoices` table) plus `ShouldBeUnique` (`uniqueId()` = document ID, `uniqueFor` = 300s).
  No `documents.external_invoice_id` column is involved.
- **Failure handling**: after exhausting retries, `failed()` logs the error and sends a
  `MoloniInvoiceFailedNotification` to the configured `invoicing.providers.moloni.alert_email`
  and to admin users with the `access settings` permission.

---

## 6. Configuration

### Environment Variables

```ini
# Required for payment processing
EASYPAY_ACCOUNT_ID=your-account-id
EASYPAY_API_KEY=your-api-key

# Optional: cosmetic "webhook configured" badge only (EasyPay webhooks are unsigned)
EASYPAY_WEBHOOK_SECRET=your-webhook-secret

# Environment mode
EASYPAY_SANDBOX=true  # Set to false for production
```

### EasyPay Backoffice Configuration

1. Log in to EasyPay backoffice
2. Navigate to: **Developers > Configuration API 2.0**
3. Select your payment account
4. Click **Notifications**
5. Set **Generic - URL** to: `https://app.example.test/api/payment/webhook/easypay`

> **Note**: EasyPay does NOT use webhook signatures. Security is achieved by
> querying their API to verify notifications are genuine.

### Rate Limiting

The webhook endpoint is rate-limited to 60 requests per minute per IP:

```php
// routes/api.php
Route::prefix('payment')->middleware('throttle:60,1')->group(function () {
    Route::prefix('webhook')->group(function () {
        Route::post('easypay', [PaymentWebhookController::class, 'easypay'])
            ->name('api.payment.webhook.easypay');
    });
});
```

This resolves to `POST /api/payment/webhook/easypay` (route name `api.payment.webhook.easypay`).

---

## 7. Admin Interface

The system includes an admin interface for payment management accessible at `/admin/payment-methods`.

### Payment Methods Management

**URL**: `/admin/payment-methods`

Allows administrators to:

- View all configured payment methods
- Enable/disable payment methods
- Edit payment method names and instructions
- View gateway configuration status

```
+------------------------------------------------------------------+
|  Payment Methods                                                  |
|                                                                   |
|  Gateway Status Cards:                                            |
|  +------------------------+  +------------------------+          |
|  |  EasyPay               |  |  Offline               |          |
|  |  Status: Configured    |  |  Status: N/A           |          |
|  |  Mode: Sandbox         |  |                        |          |
|  |  Webhook: Configured   |  |                        |          |
|  +------------------------+  +------------------------+          |
|                                                                   |
|  +--------------------------------------------------------------+|
|  | Name         | Driver  | Status  | Instructions | Actions    ||
|  |--------------|---------|---------|--------------|------------||
|  | EasyPay      | easypay | Enabled | Digital Pay..| Edit Toggle||
|  | Bank Transfer| offline | Enabled | Transfer to..| Edit Toggle||
|  +--------------------------------------------------------------+|
+------------------------------------------------------------------+
```

### Transaction Viewer

**URL**: `/admin/payment-transactions`

Provides visibility into all payment transactions:

- Statistics dashboard (total, pending, success, failed, total amount)
- Filterable transaction list
- Transaction detail view with payment data

```
Filter Options:
- Status: All / Pending / Success / Failed
- Payment Method: All / EasyPay / Bank Transfer
- Date Range: From / To
```

### Webhook Monitoring

**URL**: `/admin/webhook-logs`

Monitors all incoming webhook requests:

- Real-time statistics (total, today, success rate, avg. processing time)
- Status breakdown by webhook result
- Detailed log view with headers, payload, and response
- Processing time tracking for performance monitoring

```
Webhook Statuses:
- success: Payment processed successfully
- already_processed: Duplicate webhook handled via idempotency
- failed: Payment failed or transaction not found
- error: Processing error occurred
- invalid_signature: Signature validation failed
- acknowledged: Status update received (non-success payment status)
```

### Database Schema

The webhook monitoring uses a dedicated `webhook_logs` table. The illustrative definition below
matches the live schema in `database/schema/mysql-schema.sql`: `response_code` and
`processing_time_ms` are plain `int` columns, and both foreign keys are `ON DELETE SET NULL`.

```php
Schema::create('webhook_logs', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('gateway', 50)->index();
    $table->string('request_id', 100)->nullable()->index();
    $table->string('status', 50)->index();
    $table->string('ip_address', 45)->nullable();
    $table->json('headers')->nullable();
    $table->json('payload')->nullable();
    $table->json('response')->nullable();
    $table->char('transaction_id', 36)->nullable();
    $table->char('document_id', 36)->nullable();
    $table->text('error_message')->nullable();
    $table->integer('response_code')->nullable();
    $table->integer('processing_time_ms')->nullable();
    $table->timestamps();

    $table->foreign('transaction_id')->references('id')->on('payment_transactions')->nullOnDelete();
    $table->foreign('document_id')->references('id')->on('document')->nullOnDelete();
});
```

### Navigation

Quick links between payment admin pages:

- Payment Methods Index -> View Transactions / View Webhook Logs
- Transactions Index -> Manage Payment Methods
- Webhook Logs Index -> Manage Payment Methods

---

## 8. Testing

### Running Tests

```bash
# Run all webhook tests
php artisan test --filter=EasyPayWebhookTest

# Run all gateway tests
php artisan test --filter=EasyPayGatewayTest
```

### Test Coverage

| Test | Description |
|------|-------------|
| `marks document as paid on successful webhook` | Happy path for payment success |
| `handles failed payment webhook correctly` | Verifies failed payment handling |
| `accepts webhook without signature (EasyPay does not use signatures)` | Confirms no signature is required |
| `handles duplicate webhook with idempotency - transaction already success` | Tests Layer 1 idempotency |
| `handles duplicate webhook with idempotency - document already paid` | Tests Layer 2 idempotency |
| `dispatches DocumentMarkedAsPaid event with correct data` | Verifies event dispatch |
| `does not dispatch event for failed payment` | Ensures no event for failures |

### Manual Testing

1. Create a test payment via the application
2. Use ngrok for local development: `ngrok http 80`
3. Configure the ngrok URL in EasyPay dashboard
4. Complete a test payment
5. Check logs: `tail -f storage/logs/laravel.log | grep EasyPay`

---

## 9. Troubleshooting

### Webhook Not Received

1. Verify webhook URL is publicly accessible
2. Check HTTPS certificate is valid
3. Review EasyPay dashboard webhook logs
4. Check Laravel logs for any errors

### API Verification Fails

1. Check `EASYPAY_ACCOUNT_ID` and `EASYPAY_API_KEY` are correct
2. Verify using correct environment (sandbox vs production)
3. Check EasyPay API is accessible from your server

### Payment Not Marking as Paid

1. Check document type is 'INV' or 'ORD' (required by MarkAsPaidAction)
2. Verify transaction exists in database
3. Check for exceptions in logs
4. Ensure 'PAY' document type exists

### Duplicate Processing

1. Check logs for "already_processed" status
2. Verify idempotency checks are working
3. Check for database lock contention

### External Invoice Not Generated

1. Verify queue worker is running: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Review job implementation in `GenerateExternalInvoiceJob`

---

## 10. Key Files

### Controllers

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/PaymentWebhookController.php` | Main webhook handler |
| `app/Http/Controllers/Admin/PaymentMethodController.php` | Admin payment methods management |
| `app/Http/Controllers/Admin/PaymentTransactionController.php` | Admin transaction viewer |
| `app/Http/Controllers/Admin/WebhookLogController.php` | Admin webhook monitoring |

### Events

| File | Purpose |
|------|---------|
| `app/Events/DocumentMarkedAsPaid.php` | Dispatched after a document is marked as paid; triggers invoice generation |
| `app/Events/ActivateAfterPayment.php` | Existing event for activation flows |

### Listeners

| File | Purpose |
|------|---------|
| `app/Listeners/DispatchInvoiceGenerationListener.php` | Queues invoice job |

### Jobs

| File | Purpose |
|------|---------|
| `app/Jobs/GenerateExternalInvoiceJob.php` | External invoice API integration |

### Gateways

| File | Purpose |
|------|---------|
| `src/Domain/Payments/Gateways/EasyPayGateway.php` | EasyPay API integration |
| `src/Domain/Payments/Gateways/AbstractPaymentGateway.php` | Base gateway class |

### Models

| File | Purpose |
|------|---------|
| `src/Domain/Payments/Models/PaymentMethod.php` | Payment method configuration |
| `src/Domain/Payments/Models/PaymentTransaction.php` | Payment transaction records |
| `src/Domain/Payments/Models/WebhookLog.php` | Webhook request logging |

### Views (Admin)

| File | Purpose |
|------|---------|
| `resources/views/web/admin/payment-methods/index.blade.php` | Payment methods list |
| `resources/views/web/admin/payment-methods/edit.blade.php` | Edit payment method |
| `resources/views/web/admin/payment-transactions/index.blade.php` | Transaction list |
| `resources/views/web/admin/payment-transactions/show.blade.php` | Transaction details |
| `resources/views/web/admin/webhook-logs/index.blade.php` | Webhook logs list |
| `resources/views/web/admin/webhook-logs/show.blade.php` | Webhook log details |

### Translations

| File | Purpose |
|------|---------|
| `lang/en/payment_admin.php` | English translations for payment admin |
| `lang/pt/payment_admin.php` | Portuguese translations for payment admin |

### Actions

| File | Purpose |
|------|---------|
| `src/Domain/Documents/Actions/MarkAsPaidAction.php` | Marks documents as paid |

### Tests

| File | Purpose |
|------|---------|
| `tests/Feature/EasyPayWebhookTest.php` | Webhook integration tests |
| `tests/Feature/EasyPayGatewayTest.php` | Gateway unit tests |

### Configuration

| File | Purpose |
|------|---------|
| `config/payment.php` | Payment gateway configuration |
| `routes/api.php` | Webhook route definition |

---

## Appendix: Webhook Response Codes

| Response | Status Code | Meaning |
|----------|-------------|---------|
| `{"status": "success"}` | 200 | Payment processed successfully |
| `{"status": "failed"}` | 200 | Payment failed or verification failed |
| `{"status": "already_processed"}` | 200 | Duplicate webhook (idempotency) |
| `{"status": "acknowledged"}` | 200 | Status update received (pending) |
| `{"error": "Webhook processing failed"}` | 500 | Unexpected error |

**Note**: EasyPay expects a 2xx response within 20 seconds. Non-2xx responses trigger retries with exponential backoff.
