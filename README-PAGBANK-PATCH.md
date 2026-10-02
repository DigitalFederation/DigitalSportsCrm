# PagBank Orders API / PIX patch for DigitalSportsCrm

This patch adds **PagBank PIX** as an optional payment gateway following the
existing DigitalSportsCrm payment architecture.

## Scope

The first integration deliberately implements **Orders API + PIX** only:

- creates an Order/Charge through `POST /orders`;
- uses the local `PaymentTransaction` UUID as PagBank `reference_id`;
- sends an `x-idempotency-key` on order creation;
- displays the returned PIX QR Code and copy-and-paste string on a signed page;
- receives PagBank notifications at `/api/payment/webhook/pagbank`;
- validates `x-authenticity-token` from the **raw request body**;
- re-queries `GET /charges/{charge_id}` before marking a document as paid;
- verifies local transaction reference, BRL amount and PIX payment method;
- reuses the existing idempotent `PaymentWebhookController` completion flow.

Credit/debit-card capture is intentionally not included in this first PR because
it would require card encryption/3DS and a broader PCI/security scope.

## Files added

- `src/Domain/Payments/Gateways/PagBankGateway.php`
- `src/Domain/Payments/Handlers/PagBankPaymentHandler.php`
- `app/Http/Controllers/Payments/PagBankPaymentController.php`
- `resources/views/payments/pagbank/show.blade.php`
- `tests/Unit/Domain/Payments/Gateways/PagBankGatewayTest.php`

## Existing files changed

- `.env.example`
- `config/payment.php`
- `routes/api.php`
- `routes/web.php`
- `database/seeders/PaymentMethodSeeder.php`
- `app/Http/Controllers/Api/PaymentWebhookController.php`
- `docs/features/payments.md`

## Apply to a clean PR branch

From the DigitalSportsCrm repository:

```bash
git fetch upstream
git switch -c pr/pagbank-payments upstream/main
git status
```

The working tree should be clean.

Run the patch script from the repository root:

```bash
python3 /path/to/pagbank_orders_pix_patch/apply_pagbank_patch.py
```

Review:

```bash
git status
git --no-pager diff --stat
git --no-pager diff
git diff --check
```

Format and test:

```bash
vendor/bin/pint --dirty
php artisan test --filter=PagBank
php artisan route:list | grep -i pagbank
```

If developing inside Docker, prefix the Artisan commands with the appropriate
container command, for example:

```bash
docker compose exec app php artisan test --filter=PagBank
docker compose exec app php artisan route:list | grep -i pagbank
```

## Sandbox configuration

Obtain the Sandbox token from the PagBank Developer Portal and make sure the
PagBank account has at least one active PIX key.

Add to `.env`:

```ini
PAGBANK_ENABLED=true
PAGBANK_SANDBOX=true
PAGBANK_TOKEN=YOUR_SANDBOX_TOKEN
PAGBANK_PIX_EXPIRATION_MINUTES=30
PAGBANK_PAYMENT_PAGE_TTL_MINUTES=60
```

For local webhook testing, `APP_URL` must be a public HTTPS URL that reaches the
application. Example with a tunnel:

```ini
APP_URL=https://YOUR-TUNNEL.example
```

Then:

```bash
php artisan optimize:clear
```

### Enable the payment method in an existing database

Do **not** rerun the entire `PaymentMethodSeeder` on an existing database if
that seeder has already inserted other methods. Create/update only PagBank:

```bash
php artisan tinker --execute="\Domain\Payments\Models\PaymentMethod::withoutGlobalScopes()->updateOrCreate(
    ['driver' => 'pagbank'],
    [
        'name' => 'PagBank PIX',
        'instructions' => 'Secure PIX payment via PagBank QR Code',
        'handler' => 'Domain\\Payments\\Handlers\\PagBankPaymentHandler',
        'is_enabled' => true,
    ]
);"
```

Docker example:

```bash
docker compose exec app php artisan tinker --execute="\Domain\Payments\Models\PaymentMethod::withoutGlobalScopes()->updateOrCreate(
    ['driver' => 'pagbank'],
    [
        'name' => 'PagBank PIX',
        'instructions' => 'Secure PIX payment via PagBank QR Code',
        'handler' => 'Domain\\Payments\\Handlers\\PagBankPaymentHandler',
        'is_enabled' => true,
    ]
);"
```

Clear configuration again:

```bash
php artisan optimize:clear
```

## Verify the public webhook

The route should exist only while `PAGBANK_ENABLED=true`:

```bash
php artisan route:list | grep pagbank
```

Expected routes include:

```text
GET|HEAD  payment/pagbank/{transaction}
POST      api/payment/webhook/pagbank
```

From outside the development host, an unsigned test POST should reach the
application and be rejected with HTTP 401 rather than 404/500:

```bash
curl -i -X POST \
  -H 'Content-Type: application/json' \
  -d '{}' \
  https://YOUR-PUBLIC-HOST/api/payment/webhook/pagbank
```

## End-to-end Sandbox scenarios

PagBank's PIX Sandbox simulator selects the outcome based on the payment value.
Create test documents/invoices with the following totals:

| Amount | Expected PIX behavior |
|---|---|
| <= R$ 100.00 | paid almost immediately |
| > R$ 100.00 and <= R$ 200.00 | paid after about 5 minutes |
| > R$ 200.00 and <= R$ 300.00 | remains WAITING |
| > R$ 300.00 and <= R$ 400.00 | DECLINED |
| > R$ 400.00 | remains WAITING |

For each scenario:

1. Select `PagBank PIX` as the payment method.
2. Confirm that an order is created and the signed PIX page opens.
3. Confirm QR Code and PIX copy-and-paste text are visible for WAITING payments.
4. Check `payment_transactions`: it should initially be `pending`.
5. For a PAID scenario, wait for the webhook.
6. Confirm a `webhook_logs` entry for gateway `pagbank`.
7. Confirm the application queried PagBank and only then changed the local
   transaction to `success` and marked the document paid.
8. Re-send the notification in PagBank Sandbox and confirm idempotency: the
   document must not be processed twice.
9. Test a DECLINED amount and confirm the transaction becomes `failed` while the
   document remains unpaid.

Useful inspection commands:

```bash
php artisan tinker --execute="dump(\Domain\Payments\Models\PaymentTransaction::latest()->first()?->only(['id','document_id','amount','status','comment']));"
```

```bash
php artisan tinker --execute="dump(\Domain\Payments\Models\WebhookLog::latest()->first()?->only(['gateway','status','transaction_id','document_id','response_code','error_message']));"
```

## Manual signature test

The signature unit test implements PagBank's rule:

```text
SHA256("{PAGBANK_TOKEN}-{RAW_WEBHOOK_BODY}")
```

The incoming value is read from `x-authenticity-token`. The raw body must not be
reformatted before hashing.

## Commit and PR

After tests pass:

```bash
git add \
  .env.example \
  config/payment.php \
  routes/api.php \
  routes/web.php \
  database/seeders/PaymentMethodSeeder.php \
  app/Http/Controllers/Api/PaymentWebhookController.php \
  app/Http/Controllers/Payments/PagBankPaymentController.php \
  src/Domain/Payments/Gateways/PagBankGateway.php \
  src/Domain/Payments/Handlers/PagBankPaymentHandler.php \
  resources/views/payments/pagbank/show.blade.php \
  tests/Unit/Domain/Payments/Gateways/PagBankGatewayTest.php \
  docs/features/payments.md

git commit -m "feat: add PagBank PIX payment gateway"
git push -u origin pr/pagbank-payments
```

Create the PR with:

- base repository: `DigitalFederation/DigitalSportsCrm`
- base branch: `main`
- head repository: `luciano-ondir/DigitalSportsCrm`
- compare branch: `pr/pagbank-payments`

Suggested PR title:

```text
Add PagBank Orders API / PIX payment gateway
```

Suggested PR summary:

```text
Adds PagBank as an optional Brazil-specific payment gateway using the Orders
API with PIX. The integration creates idempotent PIX charges, presents the QR
Code/copy-and-paste value, validates webhook authenticity, and verifies the
charge directly with PagBank before marking a document as paid. Existing
offline and EasyPay gateways remain unchanged.
```
