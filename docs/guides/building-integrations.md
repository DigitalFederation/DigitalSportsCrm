---
title: Building Integrations
description: Add your own payment gateway or invoicing provider to Digital Sports CRM
---

# Building Integrations

Payments and invoicing are **extension points**, not fixed features. The core ships a country-neutral
default (offline payments, no external invoicing) and two **bundled reference integrations for
Portugal** — [EasyPay](/easypay_integration) (payments) and Moloni (invoicing). Both are **optional
and disabled unless configured**. To support another country or provider, you implement an interface
and register it in config — no core changes required.

## Enabling and disabling integrations

Every optional integration is controlled by a single `*_ENABLED` environment flag that defaults to
`false`, so a fresh install ships with all of them off:

| Integration | Flag (default) | When disabled |
|-------------|----------------|---------------|
| EasyPay (payments) | `EASYPAY_ENABLED=false` | Gateway is not registered, its webhook route is not exposed, and the `offline` gateway is used. |
| Moloni (invoicing) | `MOLONI_ENABLED=false` | No external invoices are generated for paid documents. |

To enable one, set its flag (and the provider's credentials) in `.env`; to disable one, set the flag to
`false` (or remove it) and clear the config cache (`php artisan config:clear`). Follow the same
`enabled` convention for any gateway/provider you add — the `PaymentGatewayManager` skips gateways
whose `enabled` is `false`, and each gateway's webhook route is registered only while it is enabled.

## Payment gateways

A payment gateway is any class implementing
`Domain\Payments\Contracts\PaymentGatewayInterface`. The interface:

```php
interface PaymentGatewayInterface
{
    public function configure(array $config): void;
    public function createPayment(Document $document): PaymentResponseData;
    public function verifyPayment(array $webhookData): PaymentResponseData;
    public function getName(): string;
    public function supportsWebhooks(): bool;
    public function getWebhookUrl(): ?string;
    public function validateWebhookSignature(array $headers, string $payload): bool;
}
```

Extend `Domain\Payments\Gateways\AbstractPaymentGateway` to inherit config handling
(`getConfig()`, `validateConfig()`), transaction helpers (`createPaymentTransaction()`,
`updatePaymentTransaction()`, `findTransactionByReference()`), activity logging, and sensible
webhook defaults — so you typically only implement `getName()`, `createPayment()`, and
`verifyPayment()`. The bundled `OfflineGateway` and `EasyPayGateway`
(`src/Domain/Payments/Gateways/`) are working references.

### Steps

1. **Implement the gateway** under your own namespace (or a [plugin](/guides/creating-a-plugin)):

   ```php
   namespace App\Payments\Gateways;

   use Domain\Payments\Gateways\AbstractPaymentGateway;
   use Domain\Documents\Models\Document;
   use Domain\Payments\DataTransferObject\PaymentResponseData;

   class StripeGateway extends AbstractPaymentGateway
   {
       public function getName(): string
       {
           return 'stripe';
       }

       public function createPayment(Document $document): PaymentResponseData
       {
           $this->validateConfig(['api_key']);
           // ... call the provider, persist a transaction, return a redirect/intent ...
       }

       public function verifyPayment(array $webhookData): PaymentResponseData
       {
           // ... map the provider's webhook payload to a PaymentResponseData status ...
       }
   }
   ```

2. **Register it** in `config/payment.php` — point the `gateway` key at your class. The
   `PaymentGatewayManager` registers every gateway declared here; nothing is hardcoded:

   ```php
   'gateways' => [
       'offline' => [ /* built-in default */ ],
       'stripe' => [
           'enabled' => env('STRIPE_ENABLED', false),
           'gateway' => App\Payments\Gateways\StripeGateway::class,
           'handler' => App\Payments\Handlers\StripePaymentHandler::class,
           'api_key' => env('STRIPE_API_KEY'),
       ],
   ],
   ```

3. **Make it the default** (optional) by setting `'default' => 'stripe'` or `PAYMENT_GATEWAY=stripe`.

4. **Receive webhooks**: add a route under `routes/api.php` (mirroring the EasyPay one) that resolves
   `PaymentGatewayManager::createFromConfig()->gateway('stripe')` and calls `verifyPayment()`. See
   [Payment Webhooks](/features/payment_webhook_implementation).

The `default` gateway is `offline`, which records payments without an external provider — so a fresh
install is fully functional with **no** payment integration configured.

## Invoicing providers

External invoicing is configured in `config/invoicing.php`:

```php
'default' => env('INVOICE_PROVIDER', 'moloni'),
'providers' => [
    'moloni' => [
        'enabled' => env('MOLONI_ENABLED', false),
        // ...
    ],
],
```

Invoicing is **off by default** (`MOLONI_ENABLED=false`); when disabled, paid documents simply carry no
external invoice. The bundled **Moloni** provider (`src/Domain/Invoicing/`, Portugal) is currently the
only implementation and serves as the reference: a provider authenticates, maps a paid `Document` to
the provider's invoice/receipt API, and records the result (see `CreateMoloniInvoiceReceiptAction`,
`MoloniSettingsService`, and the `GenerateExternalInvoiceJob` dispatched after payment). To add a
provider for another country, follow that pattern, add a `providers.<name>` block, and set
`INVOICE_PROVIDER=<name>`.

> A formal `InvoiceProviderInterface` (mirroring `PaymentGatewayInterface`) is a welcome contribution —
> it would let invoicing providers be registered purely from config like payment gateways.

## Keeping integrations out of core

For a fully decoupled deployment, build your integration as a [plugin](/guides/creating-a-plugin)
(a Composer package under `app/Plugins/`). The plugin ships the gateway/provider class and its config,
and the platform discovers it via `plugins:sync` — so country-specific integrations never touch core.

## See also

- [Payments](/features/payments) — the payment flow and gateway manager
- [Payment Webhooks](/features/payment_webhook_implementation) — webhook + invoice generation
- [EasyPay Integration](/easypay_integration) — the bundled Portugal payment reference
- [Creating a Plugin](/guides/creating-a-plugin) — package an integration without forking core
