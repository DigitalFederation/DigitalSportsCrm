# EasyPay Integration Guide

> **Optional, Portugal-specific reference integration.** EasyPay is a Portuguese payment
> provider, **disabled unless its `EASYPAY_*` environment variables are configured**. It is bundled
> as a working example of the payment-gateway extension point; the platform default is the `offline`
> gateway. To add a gateway for another country, see
> [Building Integrations](/guides/building-integrations).

This document describes the EasyPay payment gateway integration, including API usage, webhook handling, and authorization/capture flows.

**Last Updated**: January 2026

---

## Table of Contents

1. [Overview](#1-overview)
2. [Configuration](#2-configuration)
3. [Payment Types](#3-payment-types)
4. [Webhook Integration](#4-webhook-integration)
5. [Authorization & Capture](#5-authorization--capture)
6. [API Reference](#6-api-reference)
7. [Troubleshooting](#7-troubleshooting)
8. [Key Files](#8-key-files)

---

## 1. Overview

EasyPay is a Portuguese payment gateway supporting multiple payment methods including:
- Credit/Debit Cards (CC)
- Multibanco (MB) - Portuguese ATM network
- MB WAY - Mobile payments
- Direct Debit (DD)
- Virtual IBAN (VI)
- Apple Pay
- Google Pay
- Samsung Pay

### Integration Type

We use **Pay By Link** (`/link` endpoint) which creates payment links that customers can use to pay via multiple methods.

### API Versions

- **Sandbox**: `https://api.test.easypay.pt/2.0`
- **Production**: `https://api.prod.easypay.pt/2.0`

---

## 2. Configuration

### Environment Variables

Define these values in `.env`:

```text
EASYPAY_ACCOUNT_ID
EASYPAY_API_KEY
EASYPAY_SANDBOX=true

# Optional. EasyPay webhooks are NOT signed, so this value is not used for signature
# verification — it only drives a cosmetic "webhook configured" badge in the admin UI.
EASYPAY_WEBHOOK_SECRET
```

### Config File

Located at `config/payment.php`:

The gateway config is read via `config('payment.gateways.easypay')`, so the `easypay`
key lives under `gateways` (the file also sets `'default' => 'offline'`):

```php
'default' => 'offline',

'gateways' => [
    'easypay' => [
        'driver' => 'EasyPay',
        'handler' => Domain\Payments\Handlers\EasyPayPaymentHandler::class,
        'account_id' => env('EASYPAY_ACCOUNT_ID'),
        'api_key' => env('EASYPAY_API_KEY'),
        'webhook_secret' => env('EASYPAY_WEBHOOK_SECRET'),
        'sandbox' => env('EASYPAY_SANDBOX', true),
    ],
],
```

### Authentication

All API requests must include two HTTP headers:

| Header | Source |
|--------|--------|
| `AccountId` | `EASYPAY_ACCOUNT_ID` |
| `ApiKey` | `EASYPAY_API_KEY` |

Use sandbox values only in local `.env` files.

**Important**: keep actual sandbox credentials in `.env`; never commit them.

### EasyPay Backoffice Configuration

**Webhook URL Setup** (CRITICAL):

1. Log in to EasyPay backoffice:
   - Sandbox: `https://backoffice.test.easypay.pt/`
   - Production: `https://backoffice.easypay.pt/`
2. Navigate to: **Developers > Configuration API 2.0**
3. Select your payment account
4. Click **Notifications**
5. Set **Generic - URL** to: `https://app.example.test/api/payment/webhook/easypay`

---

## 3. Payment Types

EasyPay supports different payment flows:

### Sale (Single-Step)

Authorization and capture combined - immediate fund transfer.

```
Customer pays → Funds transferred immediately
```

**Use for**: Digital products, immediate services, SaaS subscriptions

### Authorization + Capture (Two-Step)

Funds are held first, then captured later.

```
Step 1: Authorize → Funds held on customer account
Step 2: Capture → Funds transferred to merchant
```

**Use for**: E-commerce with shipping, hotels, car rentals

### Supported Methods by Payment Type

| Payment Method | Authorization | Capture | Sale |
|----------------|---------------|---------|------|
| Credit/Debit Card | Yes | Yes | Yes |
| MB WAY | Yes | Yes | Yes |
| Apple Pay | Yes | Yes | Yes |
| Google Pay | Yes | Yes | Yes |
| Samsung Pay | Yes | Yes | Yes |
| Multibanco | No | No | Yes |
| Direct Debit | No | No | Yes |
| Virtual IBAN | No | No | Yes |

---

## 4. Webhook Integration

### How EasyPay Webhooks Work

EasyPay uses webhooks (notifications) to inform your application about payment events. This is a server-to-server service.

**IMPORTANT**: EasyPay does NOT use webhook signatures for authentication. Their security model requires you to query their API to verify notifications are genuine.

### Webhook Types

#### 1. Generic Notification (Recommended)

Notifies all state transitions: payments, authorizations, cancellations, etc.

**Payload Structure**:
```json
{
  "id": "5eca7446-14e9-47bb-aabb-5ee237159b8b",
  "key": "<notification-key>",
  "type": "capture",
  "status": "success",
  "messages": ["Your request was successfully captured"],
  "date": "2022-08-10 14:56:54"
}
```

| Field | Description |
|-------|-------------|
| `id` | Payment ID (Single/Link ID) |
| `key` | Merchant key from create request |
| `type` | Event type: `capture`, `authorisation`, `sale`, etc. |
| `status` | Result: `success`, `failed` |
| `messages` | Human-readable messages |
| `date` | Event timestamp |

#### 2. Authorization Notification

Specific to authorization events only.

```json
{
  "id": "1bbc14c3-8ca8-492c-887d-1ca86400e4fa",
  "value": 1,
  "currency": "EUR",
  "key": "the merchant key",
  "expiration_time": "2022-01-01 10:20",
  "customer": {
    "id": "22ea3cc9-424b-489a-91b7-8955f643dc93",
    "name": "Customer Example",
    "email": "customer@example.com",
    "phone": "911234567",
    "phone_indicative": "+351"
  },
  "method": "mb",
  "account": { "id": "4c67e74b-a256-4e0a-965d-97bf5d01bd50" },
  "authorisation": { "id": "4c67e74b-a256-4e0a-965d-97bf5d01bd50" }
}
```

#### 3. Transaction Notification

Specific to capture/payment events only.

```json
{
  "id": "87615356-0a88-42bd-8abb-aab3e90128de",
  "value": "40",
  "currency": "EUR",
  "key": "the merchant key",
  "expiration_time": "2023-08-07 20:00",
  "method": "MBW",
  "customer": {
    "id": "2eb64a7f-90a7-4dc6-959b-1d9aba44910c",
    "phone": "910410419"
  },
  "account": { "id": "0b8de6e7-89c8-4d76-93e8-019bc058f27d" },
  "transaction": {
    "id": "eb23923b-3529-4b71-b54e-1e707a8d55c4",
    "key": "transaction_key_of_this_capture",
    "type": "capture",
    "date": "2022-08-10T12:45:50Z",
    "values": {
      "requested": "40",
      "paid": "40",
      "fixed_fee": "0",
      "variable_fee": "0",
      "tax": "0",
      "transfer": "0"
    }
  }
}
```

### Our Webhook Implementation

**Endpoint**: `POST /api/payment/webhook/easypay`

**Security Flow**:
1. Receive webhook from EasyPay
2. Accept request (no signature validation - EasyPay doesn't use signatures)
3. Query EasyPay API to verify notification is genuine
4. Process payment based on verified API response

```php
// EasyPayGateway::verifyPayment() flow:
1. Extract payment ID from webhook
2. Find local transaction by ID
3. Query EasyPay API: GET /link/{id} or GET /single/{id}
4. Use API response status (not webhook status) for processing
```

### Webhook Events

| Event | Trigger |
|-------|---------|
| `capture` | Payment successfully captured |
| `authorisation` | Authorization granted |
| `sale` | Single-step payment completed |
| `failed` | Payment failed |
| `cancelled` | Payment cancelled |
| `expired` | Payment link/reference expired |

### Generic Notification ID/Key by Payment Type

| Payment Type | id | key |
|--------------|----|----|
| Single Authorisation | Single payment ID | `key` from create request |
| Single Capture | Single payment ID | `transaction_key` from capture request |
| Single Sale | Single payment ID | `transaction_key` from `capture` object in create request |
| Frequent Create | Frequent payment ID | `key` from create request |
| Frequent Authorization | Frequent payment ID | `transaction_key` from authorization request |
| Frequent Capture | Capture operation ID | `transaction_key` from capture request |
| Refund | Refund ID | `transaction_key` from refund request |
| Void | Void ID | `transaction_key` from void request |
| Subscription Create | Subscription ID | `key` from create request |
| Subscription Capture | Subscription ID | `transaction_key` from `capture` object |
| Chargeback Single | Single ID | `transaction_key` from create request |
| Chargeback Frequent | Capture operation ID | `transaction_key` from capture request |
| Out Payment | Out payment ID | `key` from create request |

---

## 5. Authorization & Capture

### Authorization

Verifies customer has funds and puts them on hold:
- No money is transferred
- Hold expires if not captured (typically 7 days)
- Customer cannot use held funds elsewhere

### Capture

Transfers the held funds to merchant:
- Can capture full or partial amount
- Remaining authorization is released
- Triggers payment notification

### Multi-Capture

Split an authorization into multiple captures:

```
Authorize: EUR 150
├── Capture 1: EUR 75 (Vendor A ships)
└── Capture 2: EUR 75 (Vendor B ships)
```

### Partial Capture

Capture less than authorized:

```
Authorize: EUR 100
Capture: EUR 75
Result: EUR 75 captured, EUR 25 released
```

### Voiding Authorization

Cancel an authorization before expiry:

```bash
POST /void/{authorization-id}
{
  "descriptive": "Customer cancelled order"
}
```

### Hold Periods

| Payment Method | Hold Period |
|----------------|-------------|
| Debit Card | 3-7 days |
| Credit Card | 7-30 days |
| MB WAY | 7 days |

---

## 6. API Reference

### Pay By Link - Create Payment Link

**Endpoint**: `POST /link`

**Request Body** (API v2.0 - Updated January 2026):
```json
{
  "type": "SINGLE",
  "expiration_time": "2026-06-10T09:27:55.339Z",
  "customer": {
    "name": "Example Customer",
    "email": "customer@example.test",
    "phone": "+15550101000",
    "language": "PT"
  },
  "communication_channels": ["EMAIL"],
  "payment": {
    "methods": ["CC", "MB", "MBW", "AP", "GP"],
    "capture": {
      "descriptive": "Invoice #12345",
      "key": "your-transaction-id"
    },
    "single": {
      "requested_amount": "50.00"
    }
  }
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `type` | string | Yes | Payment type: `SINGLE`, `FREQUENT`, or `SUBSCRIPTION` |
| `expiration_time` | string | No | RFC3339 formatted expiry date |
| `customer.name` | string | Yes | Customer name |
| `customer.email` | string | Yes | Customer email |
| `customer.phone` | string | Yes | Customer phone (E.164 format) |
| `customer.language` | string | Yes | Customer language (ISO 639-1, e.g., `PT`, `EN`) |
| `communication_channels` | array | Yes | `["EMAIL"]`, `["SMS"]`, or both (can be empty) |
| `payment.methods` | array | Yes | Payment methods: `CC`, `MB`, `MBW`, `DD`, `VI`, `AP`, `GP` |
| `payment.capture.descriptive` | string | No | Description for the capture operation |
| `payment.capture.key` | string | No | Your internal reference (echoed in webhooks) |
| `payment.single.requested_amount` | string | Yes | Amount as string with 2 decimals (e.g., `"50.00"`) |

**Response**:
```json
{
  "id": "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8",
  "url": "https://easypay.pt/s/<short-code>",
  "image": "https://cdn.easypay.pt/images/qr/<short-code>",
  "status": "ACTIVE",
  "type": "SINGLE",
  "created_at": "2025-06-16T13:32:32Z",
  "customer": { ... },
  "payment": { ... }
}
```

**Link Statuses**:

| Status | Description |
|--------|-------------|
| `ACTIVE` | Link is active and can be paid |
| `FINALIZED` | Payment completed successfully |
| `EXPIRED` | Link has expired |
| `DISABLED` | Link was manually cancelled |

### Pay By Link - Retrieve Link Details

```bash
GET /link/{id}
```

### Pay By Link - Update a Link

```bash
PATCH /link/{id}
{
  "description": "Updated description",
  "expiration_time": "2026-06-10T09:27:55.339Z"
}
```

### Pay By Link - Cancel a Link

```bash
DELETE /link/{id}
```

### Single Payment - Create

```bash
POST /single
{
  "type": "sale",
  "value": 10.00,
  "currency": "EUR",
  "method": "cc",
  "customer": {
    "name": "Example Customer",
    "email": "customer@example.test"
  }
}
```

**Response**:
```json
{
  "status": "success",
  "id": "5eca7446-14e9-47bb-aabb-5ee237159b8b",
  "method": "cc",
  "customer": {
    "name": "Example Customer",
    "email": "customer@example.test"
  }
}
```

### Get Payment Status

```bash
GET /link/{id}
# or
GET /single/{id}
```

**Response includes**:
- `status`: Link status (ACTIVE, PAID, EXPIRED, etc.)
- `payment_status`: Payment status (pending, paid, failed)
- `captures[]`: Array of capture details
- `transactions[]`: Array of transaction details

### Capture Authorized Payment

```bash
POST /capture/{single-id}
{
  "value": 100.00,
  "descriptive": "Order #12345"
}
```

### Void Authorization

```bash
POST /void/{single-id}
{
  "descriptive": "Cancellation reason"
}
```

### Refund

```bash
POST /refund/{capture-id}
{
  "value": 50.00,
  "iban": "PT50..."  // Optional, for bank transfer refund
}
```

---

## 7. Troubleshooting

### Common HTTP Status Codes

| Status Code | Meaning | What to Do |
|-------------|---------|------------|
| `200 OK` | Request successful | Process the response |
| `201 Created` | Resource created successfully | Process the newly created resource |
| `400 Bad Request` | Invalid request parameters | Check your request payload |
| `403 Forbidden` | Authentication failed | Verify your credentials |
| `404 Not Found` | Resource doesn't exist | Check the resource ID |
| `409 Conflict` | Request conflicts with current state | Review the conflict details |
| `429 Too Many Requests` | Rate limit exceeded | Slow down your requests |
| `500 Internal Server Error` | Server error | Retry or contact support |

### Error Response Format

```json
{
  "type": "https://docs.easypay.pt/api/overview#invalid-json",
  "title": "Invalid JSON provided",
  "detail": "Request body contains unknown field \"value\"",
  "status": 400
}
```

### Webhook Not Received

1. **Check Backoffice Configuration**:
   - Verify Generic URL is set correctly
   - Path: Developers > Configuration API 2.0 > Notifications

2. **Verify URL Accessibility**:
   ```bash
   curl -X POST https://app.example.test/api/payment/webhook/easypay \
     -H "Content-Type: application/json" \
     -d '{"test": true}'
   ```
   Should return response (even if error - confirms endpoint is reachable)

3. **Check EasyPay Webhook Logs**:
   - Backoffice: Developers > Notifications API 2.0
   - View delivery status and response codes

4. **Check Laravel Logs**:
   ```bash
   tail -f storage/logs/laravel.log | grep EasyPay
   ```

### Payment Not Marking as Paid

1. **Check Transaction Exists**:
   - Verify payment was created in `payment_transactions` table
   - Check `comment` field contains EasyPay Link ID

2. **Check Document Status**:
   - Document type must be 'INV' or 'ORD'
   - Document must not already be paid

3. **Check Webhook Logs**:
   - View `webhook_logs` table for processing details
   - Check `status` and `error_message` fields

### API Verification Failing

1. **Check API Credentials**:
   - Verify `EASYPAY_ACCOUNT_ID` and `EASYPAY_API_KEY` are correct
   - Ensure using correct environment (sandbox vs production)

2. **Check Payment ID Format**:
   - Payment IDs are UUIDs
   - Verify the ID matches what's stored in transaction

### Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Transaction not found" | Payment ID not in our database | Check payment was created and `key` field was set |
| "Could not verify payment with EasyPay" | API query failed | Check credentials and connectivity |
| "Request body contains unknown field" | Invalid JSON or wrong Content-Type | Use `->asJson()` in Laravel HTTP client |

### Webhook ID vs Link ID (IMPORTANT)

When using Pay By Link, EasyPay sends **different IDs** in webhooks than the link ID returned during creation:

1. **Creation**: You get a `link_id` (e.g., `4bdc3ae8-...`)
2. **Webhook**: EasyPay sends a `payment_page` or `capture` ID (e.g., `9b7e3494-...`)

These are NOT the same. To correlate webhooks with transactions, we use the `key` field:

- **Creating**: Set `key` to our transaction ID in the link request
- **Webhook**: EasyPay echoes back the `key` field in notifications
- **Lookup**: Find transaction by the `key` value (our transaction ID)

```php
// In buildLinkData() - set key for correlation
return [
    'value' => $amount,
    'key' => $transaction->id, // CRITICAL: This is echoed back in webhooks
    // ...
];

// In verifyPayment() - lookup by key first
if (!empty($webhookData['key'])) {
    $transaction = PaymentTransaction::find($webhookData['key']);
}
```

Our webhook handler also has fallback strategies if `key` is not present:
1. **Link ID** from EasyPay API response
2. **Reference search** in transaction comment/payment_data
3. **Amount match** as last resort

### Retry Logic

Some errors are safe to retry:

**Safe to Retry**:
- `409 Conflict` - May indicate the original request is still in transit
- `429 Too Many Requests` - Wait and retry
- `502 Bad Gateway` - Temporary server issue
- `503 Service Unavailable` - Service temporarily down

**Do NOT Retry**:
- `400 Bad Request` - Fix your request first
- `403 Forbidden` - Fix authentication first
- `404 Not Found` - Resource doesn't exist

---

## 8. Key Files

### Gateway Implementation

| File | Purpose |
|------|---------|
| `src/Domain/Payments/Gateways/EasyPayGateway.php` | Main gateway class |
| `src/Domain/Payments/Gateways/AbstractPaymentGateway.php` | Base gateway class |

### Webhook Handling

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/PaymentWebhookController.php` | Webhook controller |
| `routes/api.php` | Webhook route definition |

### Models

| File | Purpose |
|------|---------|
| `src/Domain/Payments/Models/PaymentTransaction.php` | Transaction records |
| `src/Domain/Payments/Models/PaymentMethod.php` | Payment method config |
| `src/Domain/Payments/Models/WebhookLog.php` | Webhook logging |

### Configuration

| File | Purpose |
|------|---------|
| `config/payment.php` | Gateway configuration |

### Tests

| File | Purpose |
|------|---------|
| `tests/Feature/EasyPayWebhookTest.php` | Webhook integration tests |
| `tests/Feature/EasyPayGatewayTest.php` | Gateway unit tests |

---

## Best Practices

### Security

1. **Always Verify via API**: Never trust webhook data alone
2. **Use HTTPS**: Ensure webhook endpoint uses HTTPS
3. **Idempotent Processing**: Handle duplicate webhooks gracefully
4. **Log Everything**: Keep detailed logs for troubleshooting

### Authorization & Capture

1. **Capture Promptly**: Don't wait until the last day of hold period
2. **Void Unused**: Don't leave authorizations to expire naturally
3. **Use Descriptive Text**: Help customers identify transactions
4. **Monitor Hold Periods**: Track authorizations to ensure timely captures

### Webhooks

1. **Respond Quickly**: Return 200 within 20 seconds
2. **Process Async**: Queue heavy processing for after response
3. **Handle Retries**: EasyPay retries failed webhooks with exponential backoff

---

## Payment Methods Reference

### Credit/Debit Card

A synchronous pull payment method with instant confirmation.

**Characteristics**:
- Type: Synchronous Pull
- Instant confirmation
- Supports authorizations and captures
- Supports 3D Secure authentication
- Works with: Single, Frequent, and Subscription payments

**Test Card Numbers**:
- `0000000000000000` - Authorized for all operations
- `2222222222222222` - Proceed with 3DS authentication
- `1111111111111111` - Failed for all operations
- `1234123412341234` - Declined for all operations

### MB WAY

Portuguese mobile payment method requiring a smartphone app.

**Characteristics**:
- Type: Synchronous Pull
- Instant confirmation
- Requires Portuguese bank account
- Requires MB WAY mobile app
- Supports authorizations and captures
- Works with: Single and Frequent payments

**Test Phone Numbers**:
- `911234567` - Authorized for all operations
- `917654321` - Failed for all operations
- `913456789` - Declined for all operations
- `919876543` - Pending for all operations

### Multibanco

Portuguese ATM network payment method.

**Characteristics**:
- Type: Asynchronous Push
- Customer-initiated payment
- Uses entity and reference numbers
- Works with: Single and Frequent payments

**Important Edge Cases**:
- Customer can pay a different amount than requested
- Multibanco refunds are not currently supported

### Apple Pay

Mobile payment service by Apple Inc.

**Characteristics**:
- Type: Synchronous Pull
- Instant confirmation
- Requires Apple device
- Uses tokenization for security
- Works with: Single payments

### Google Pay

Mobile payment method using cards stored in Google Account.

**Characteristics**:
- Type: Synchronous Pull
- Instant confirmation
- Tokenized for enhanced security
- SCA compliant
- Works with: Single, Frequent, and Subscription payments

### Samsung Pay

Mobile payment service for Samsung devices.

**Characteristics**:
- Type: Synchronous Pull
- Instant confirmation
- Requires Samsung device
- Uses digital tokens for security
- Works with: Single payments

### Direct Debit

SEPA Direct Debit payment method.

**Characteristics**:
- Type: Asynchronous Pull
- Requires SEPA Direct Debit Mandate
- Confirmation may take up to 14 days
- Works with: Single, Frequent, and Subscription payments

**Test IBAN** (will fail):
- `PT50000201231234567890154`

### Virtual IBAN

SEPA Bank Transfer with virtual account number.

**Characteristics**:
- Type: Asynchronous Push
- Provides virtual IBAN for each payment
- Protects your bank account details
- Works with: Single and Frequent payments

### Payment Method Comparison

| Payment Method | Type | Speed | Auth/Capture | Payment Types |
|----------------|------|-------|--------------|---------------|
| Credit/Debit Card | Pull | Instant | Yes | Single, Frequent, Subscription |
| Apple Pay | Pull | Instant | No | Single |
| Google Pay | Pull | Instant | No | Single, Frequent, Subscription |
| Samsung Pay | Pull | Instant | No | Single |
| MB WAY | Pull | Instant | Yes | Single, Frequent |
| Multibanco | Push | Async | No | Single, Frequent |
| Direct Debit | Pull | Async (up to 14 days) | No | Single, Frequent, Subscription |
| Virtual IBAN | Push | Async | No | Single, Frequent |

---

## External Documentation

- [EasyPay Docs](https://docs.easypay.pt/)
- [Webhooks Guide](https://docs.easypay.pt/docs/guides/webhooks)
- [Authorization & Capture](https://docs.easypay.pt/docs/guides/authorizations-captures)
- [API Reference](https://docs.easypay.pt/openapi)
