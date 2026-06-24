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

## 3. Webhook Implementation Details

For detailed information about the webhook callback implementation, including:

- Idempotency protection
- External invoice API integration
- Event-driven architecture for payment notifications
- Troubleshooting guide

See: [Payment Webhook Implementation](./payment_webhook_implementation.md)
