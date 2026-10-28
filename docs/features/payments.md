# Payment System

This document describes the extensible payment gateway architecture and the specific integration with EasyPay. EasyPay is an optional, Portugal-specific gateway; the platform default is the `offline` gateway (`config/payment.php`), and additional gateways can be added (see "Adding New Gateways" below, or the [Building Integrations](/guides/building-integrations) guide).

---

## 1. Payment System Architecture

The payment system is designed to be flexible and accommodate multiple payment gateways.

### Core Components

-   **`PaymentGatewayInterface`**: A contract that all payment gateways must implement, defining methods for creating and verifying payments.
-   **`PaymentResponseData`**: A standardized DTO for all payment operation responses.
-   **`AbstractPaymentGateway`**: A base class providing common functionality like configuration management, transaction logging, and webhook validation.
-   **`PaymentGatewayManager`**: A centralized service for registering and instantiating payment gateways.

### Available Gateways

1.  **EasyPay Gateway (`Domain\Payments\Gateways\EasyPayGateway`)**
    *   Integrates with EasyPay Checkout API.
    *   Supports credit cards, Multibanco, MBWay, etc.
    *   Handles webhooks by verifying payments against the EasyPay API (EasyPay does not use signatures).
    *   Supports sandbox and production modes.

2.  **Offline Gateway (`Domain\Payments\Gateways\OfflineGateway`)**
    *   For manual payment processing.
    *   Displays instructions to the user.
    *   Creates a pending transaction for manual confirmation.

### Payment Flow

1.  **Initiation**: The user selects a payment method. The `InitiatePaymentAction` calls the appropriate gateway handler.
2.  **Processing**: The gateway creates a payment session (e.g., with EasyPay). The user is redirected if necessary.
3.  **Webhook**: The gateway receives a webhook notification from the payment provider. It validates the signature and verifies the payment status.
4.  **Completion**: If the payment is successful, the associated document (e.g., subscription invoice) is marked as paid, and the service is activated.

### Currency

The displayed currency is an installation-wide presentation setting (`config/currency.php`,
documented under [Localization and Geography](/guides/localization-and-geography#currency)). It is
**not** transmitted to gateways or invoicing providers, and it does not influence what a provider
charges or issues.

This matters because two bundled integrations operate in Euro only:

-   **EasyPay** (`config/payment.php`) is a Portugal-specific gateway.
-   **Moloni** (`config/invoicing.php`, `api.moloni.pt`) is a Portuguese e-invoicing provider.

Enabling either while `CURRENCY_CODE` is set to something other than `EUR` produces an installation
that **displays one currency and charges or invoices in another** — a page reading `R$ 250,00` would
result in a €250 invoice. Nothing in the platform detects or prevents this combination.

Both integrations are opt-in and disabled by default (`EASYPAY_ENABLED=false`,
`MOLONI_ENABLED=false`), so this only arises when an operator deliberately enables a
Portugal-specific provider. If your installation bills in a currency other than the Euro, use the
`offline` gateway or supply a gateway that operates in your currency (see "Adding New Gateways"
below).

### Adding New Gateways

1.  Create a new gateway class extending `AbstractPaymentGateway`.
2.  Register the gateway with the `PaymentGatewayManager`.
3.  Add configuration details in `config/payment.php`.
4.  Create a corresponding payment handler.
5.  Add the new payment method to the `payment_method` database table.

---

## 2. EasyPay Integration Guide

This section details the specific configuration and usage of the EasyPay payment gateway.

### Prerequisites

*   An active EasyPay merchant account.
*   API Credentials: Account ID, API Key, and Webhook Secret.
*   HTTPS endpoint for webhooks (use ngrok for local development).

### Configuration

Add the following to your `.env` file:

```ini
# EasyPay Configuration
EASYPAY_ACCOUNT_ID=your-easypay-account-id
EASYPAY_API_KEY=your-easypay-api-key
# Optional: not used for signature verification (EasyPay webhooks are unsigned);
# only drives a cosmetic "webhook configured" badge in the admin UI.
EASYPAY_WEBHOOK_SECRET=your-webhook-secret
EASYPAY_SANDBOX=true
```

*   `EASYPAY_SANDBOX`: Set to `true` for testing, `false` for production.

Ensure the EasyPay payment method exists in the database:

```sql
INSERT INTO payment_method (name, driver, handler, is_enabled, instructions) VALUES 
('EasyPay', 'easypay', 'Domain\Payments\Handlers\EasyPayPaymentHandler', 1, 'Secure payment via EasyPay...');
```

### Webhook Setup

*   **Production**: Configure the webhook URL `https://app.example.test/api/payment/webhook/easypay` in your EasyPay dashboard.
*   **Development**: Use a tool like `ngrok` to expose your local server and provide the generated HTTPS URL to EasyPay.

### Testing

*   With `EASYPAY_SANDBOX=true`, all transactions are simulated in the EasyPay test environment.
*   Use EasyPay's provided test card numbers.
*   Test the full flow: initiate a payment, complete it on the EasyPay checkout page, and verify that the webhook is received and the service is activated.

### Troubleshooting

*   **Webhook Not Received**: Check that your webhook URL is publicly accessible and your HTTPS certificate is valid. Review logs in the EasyPay dashboard and your application.
*   **Webhook Verification Fails**: EasyPay notifications are verified by querying the EasyPay API. Ensure `EASYPAY_ACCOUNT_ID`, `EASYPAY_API_KEY`, and `EASYPAY_SANDBOX` match the EasyPay environment that sends the webhook.
*   **API Authentication Errors**: Verify your `EASYPAY_ACCOUNT_ID` and `EASYPAY_API_KEY` are correct and the key is active.

### Security

*   **Always verify webhook notifications against the payment provider API before marking documents as paid.**
*   Use HTTPS for all webhook endpoints.
*   Store API keys and secrets securely in environment variables.
*   Follow PCI DSS guidelines for handling any card data.

---

## 3. PagBank Integration Guide

PagBank is an optional, Brazil-specific gateway using the **Orders & Payments API**. The bundled implementation intentionally starts with **PIX** so card data never passes through Digital Sports CRM.

### Prerequisites

- A PagBank account with at least one active PIX key.
- A Sandbox authentication token from the PagBank Developer Portal.
- A public HTTPS URL for webhook delivery (a tunnel such as ngrok can be used in development).

### Configuration

Add the following to `.env`:

```ini
PAGBANK_ENABLED=true
PAGBANK_SANDBOX=true
PAGBANK_TOKEN=your-sandbox-token
PAGBANK_PIX_EXPIRATION_MINUTES=30
PAGBANK_PAYMENT_PAGE_TTL_MINUTES=60
```

Then clear cached configuration and enable the `PagBank PIX` payment method in the database/admin interface.

The webhook URL sent with each order is:

```text
https://app.example.test/api/payment/webhook/pagbank
```

### Security

- PagBank webhook authenticity is checked using the `x-authenticity-token` SHA-256 signature over `{token}-{raw_payload}`.
- A successful webhook is **not trusted by itself**. Before a document is marked as paid, the gateway queries `GET /charges/{charge_id}` and verifies the charge reference, amount, currency and payment method.
- The API token is read only from environment configuration and is never sent to the browser.
- The create-order request uses an idempotency key derived from the local payment transaction UUID.

### Sandbox testing

The PagBank Sandbox simulator can exercise PIX status changes based on transaction value. Test at least:

- an immediately paid PIX;
- a delayed payment;
- a waiting payment;
- a declined payment.

Confirm that duplicate webhook deliveries remain idempotent and that only a verified `PAID` charge marks the document as paid.

---

## 4. Webhook Implementation Details

For detailed information about the webhook callback implementation, including:

- Idempotency protection
- External invoice API integration
- Event-driven architecture for payment notifications
- Troubleshooting guide

See: [Payment Webhook Implementation](./payment_webhook_implementation.md)
