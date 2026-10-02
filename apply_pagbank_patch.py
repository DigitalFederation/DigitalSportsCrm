#!/usr/bin/env python3
"""
Apply the PagBank Orders API / PIX integration to a clean DigitalSportsCrm
working tree based on current upstream main.

Run from the repository root:
    python3 /path/to/apply_pagbank_patch.py
"""
from __future__ import annotations

import re
import shutil
import subprocess
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
SOURCE = HERE / "files"
REPO = Path.cwd()


def fail(message: str) -> None:
    print(f"ERROR: {message}", file=sys.stderr)
    raise SystemExit(1)


def read(rel: str) -> str:
    path = REPO / rel
    if not path.exists():
        fail(f"Expected file not found: {rel}")
    return path.read_text(encoding="utf-8")


def write(rel: str, content: str) -> None:
    path = REPO / rel
    path.write_text(content, encoding="utf-8")


def replace_once(content: str, old: str, new: str, rel: str) -> str:
    if old not in content:
        fail(f"Expected patch context not found in {rel}. Upstream may have changed.")
    if content.count(old) != 1:
        fail(f"Patch context is ambiguous in {rel}.")
    return content.replace(old, new, 1)


def ensure_git_repo() -> None:
    result = subprocess.run(
        ["git", "rev-parse", "--show-toplevel"],
        cwd=REPO,
        text=True,
        capture_output=True,
    )
    if result.returncode != 0:
        fail("Run this script from inside the DigitalSportsCrm Git repository.")
    top = Path(result.stdout.strip()).resolve()
    if top != REPO.resolve():
        fail(f"Run the script from the repository root: {top}")

    status = subprocess.run(
        ["git", "status", "--porcelain"],
        cwd=REPO,
        text=True,
        capture_output=True,
        check=True,
    ).stdout
    if status.strip():
        fail("Working tree is not clean. Commit/stash your work before applying this patch.")


def build_modifications() -> dict[str, str]:
    changes: dict[str, str] = {}

    # config/payment.php
    rel = "config/payment.php"
    content = read(rel)
    if "'pagbank' => [" in content:
        fail(f"{rel} already contains a PagBank gateway.")
    anchor = '''        'easypay' => [
            'driver' => 'EasyPay',
            // Portugal-specific reference gateway: disabled unless explicitly enabled.
            'enabled' => env('EASYPAY_ENABLED', false),
            'gateway' => Domain\\Payments\\Gateways\\EasyPayGateway::class,
            'handler' => Domain\\Payments\\Handlers\\EasyPayPaymentHandler::class,
            'account_id' => env('EASYPAY_ACCOUNT_ID'),
            'api_key' => env('EASYPAY_API_KEY'),
            'webhook_secret' => env('EASYPAY_WEBHOOK_SECRET'),
            'sandbox' => env('EASYPAY_SANDBOX', true),
        ],
'''
    addition = anchor + '''        'pagbank' => [
            'driver' => 'PagBank',
            // Brazil-specific Orders API / PIX gateway: disabled unless explicitly enabled.
            'enabled' => env('PAGBANK_ENABLED', false),
            'gateway' => Domain\\Payments\\Gateways\\PagBankGateway::class,
            'handler' => Domain\\Payments\\Handlers\\PagBankPaymentHandler::class,
            'token' => env('PAGBANK_TOKEN'),
            'sandbox' => env('PAGBANK_SANDBOX', true),
            'pix_expiration_minutes' => (int) env('PAGBANK_PIX_EXPIRATION_MINUTES', 30),
            'payment_page_ttl_minutes' => (int) env('PAGBANK_PAYMENT_PAGE_TTL_MINUTES', 60),
        ],
'''
    changes[rel] = replace_once(content, anchor, addition, rel)

    # routes/api.php
    rel = "routes/api.php"
    content = read(rel)
    if "api.payment.webhook.pagbank" in content:
        fail(f"{rel} already contains a PagBank webhook route.")
    anchor = '''    if (config('payment.gateways.easypay.enabled')) {
        Route::post('easypay', [PaymentWebhookController::class, 'easypay'])->name('api.payment.webhook.easypay');
    }
'''
    addition = anchor + '''    if (config('payment.gateways.pagbank.enabled')) {
        Route::post('pagbank', [PaymentWebhookController::class, 'pagbank'])->name('api.payment.webhook.pagbank');
    }
'''
    changes[rel] = replace_once(content, anchor, addition, rel)

    # routes/web.php
    rel = "routes/web.php"
    content = read(rel)
    if "payment.pagbank.show" in content:
        fail(f"{rel} already contains the PagBank payment page route.")
    import_anchor = "use App\\Http\\Controllers\\OnboardingController;\n"
    content = replace_once(
        content,
        import_anchor,
        import_anchor + "use App\\Http\\Controllers\\Payments\\PagBankPaymentController;\n",
        rel,
    )
    route_anchor = "Route::get('data-sharing-policy', [LegalController::class, 'dataSharingPolicy'])->name('data-sharing-policy');\n"
    route_addition = route_anchor + '''
// Signed public page used to display the PagBank PIX QR Code.
Route::get('/payment/pagbank/{transaction}', PagBankPaymentController::class)
    ->middleware(['signed', 'throttle:60,1'])
    ->name('payment.pagbank.show');
'''
    content = replace_once(content, route_anchor, route_addition, rel)
    changes[rel] = content

    # PaymentMethodSeeder.php
    rel = "database/seeders/PaymentMethodSeeder.php"
    content = read(rel)
    if "'driver' => 'pagbank'" in content:
        fail(f"{rel} already contains a PagBank payment method.")
    anchor = '''            [
                'name' => 'EasyPay',
                'driver' => 'easypay',
                'instructions' => 'Secure online payment with credit card, Multibanco, MBWay, and other methods',
                'handler' => 'Domain\\Payments\\Handlers\\EasyPayPaymentHandler',
            ],
'''
    addition = anchor + '''            [
                'name' => 'PagBank PIX',
                'driver' => 'pagbank',
                'instructions' => 'Secure PIX payment via PagBank QR Code',
                'handler' => 'Domain\\Payments\\Handlers\\PagBankPaymentHandler',
                // Keep optional country/provider gateways disabled until credentials are configured.
                'is_enabled' => false,
            ],
'''
    changes[rel] = replace_once(content, anchor, addition, rel)

    # .env.example
    rel = ".env.example"
    content = read(rel)
    if "PAGBANK_ENABLED=" in content:
        fail(f"{rel} already contains PagBank settings.")
    anchor = '''EASYPAY_WEBHOOK_SECRET=

MOLONI_ENABLED=false
'''
    addition = '''EASYPAY_WEBHOOK_SECRET=

# PagBank (Brazil-specific Orders API / PIX) — optional.
PAGBANK_ENABLED=false
PAGBANK_SANDBOX=true
PAGBANK_TOKEN=
PAGBANK_PIX_EXPIRATION_MINUTES=30
PAGBANK_PAYMENT_PAGE_TTL_MINUTES=60

MOLONI_ENABLED=false
'''
    changes[rel] = replace_once(content, anchor, addition, rel)

    # PaymentWebhookController.php: refactor the EasyPay-only entry method into a generic processor.
    rel = "app/Http/Controllers/Api/PaymentWebhookController.php"
    content = read(rel)
    if "public function pagbank(Request $request)" in content:
        fail(f"{rel} already contains a PagBank webhook method.")

    pattern = re.compile(
        r"    public function easypay\(Request \$request\): JsonResponse\n"
        r"    \{.*?\n"
        r"    \}\n\n"
        r"    private function handleSuccessfulPayment",
        re.S,
    )
    match = pattern.search(content)
    if not match:
        fail(f"Could not find the EasyPay webhook method in {rel}. Upstream may have changed.")

    generic = r'''    public function easypay(Request $request): JsonResponse
    {
        return $this->processWebhook($request, 'easypay', 'EasyPay');
    }

    public function pagbank(Request $request): JsonResponse
    {
        return $this->processWebhook($request, 'pagbank', 'PagBank');
    }

    private function processWebhook(Request $request, string $gatewayName, string $gatewayLabel): JsonResponse
    {
        $requestId = uniqid('webhook_', true);
        $startTime = microtime(true);
        $webhookLog = null;

        try {
            Log::info("{$gatewayLabel} webhook received", [
                'request_id' => $requestId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::debug("{$gatewayLabel} webhook payload", [
                'request_id' => $requestId,
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'payload' => $request->getContent(),
            ]);

            $webhookLog = WebhookLog::create([
                'gateway' => $gatewayName,
                'request_id' => $requestId,
                'status' => 'processing',
                'ip_address' => $request->ip(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'payload' => json_decode($request->getContent(), true),
            ]);

            $gatewayManager = PaymentGatewayManager::createFromConfig();
            $gateway = $gatewayManager->gateway($gatewayName);

            if (! $gateway->validateWebhookSignature($request->headers->all(), $request->getContent())) {
                Log::warning("{$gatewayLabel} webhook signature validation failed", [
                    'request_id' => $requestId,
                    'ip' => $request->ip(),
                ]);

                $this->updateWebhookLog($webhookLog, 'invalid_signature', $startTime, ['error' => 'Invalid signature'], 401);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $webhookData = $request->json()->all();
            $paymentResponse = $gateway->verifyPayment($webhookData);

            if ($paymentResponse->isSuccess()) {
                return $this->handleSuccessfulPayment($paymentResponse, $webhookData, $requestId, $webhookLog, $startTime);
            }

            if ($paymentResponse->isFailed()) {
                return $this->handleFailedPayment($paymentResponse, $requestId, $webhookLog, $startTime);
            }

            Log::info("{$gatewayLabel} payment status update", [
                'request_id' => $requestId,
                'status' => $paymentResponse->status,
                'transaction_id' => $paymentResponse->transactionId,
            ]);

            $this->updateWebhookLog($webhookLog, 'acknowledged', $startTime, ['status' => 'acknowledged'], 200, $paymentResponse->transactionId);

            return response()->json(['status' => 'acknowledged'], 200);
        } catch (\Exception $e) {
            Log::error("{$gatewayLabel} webhook processing failed", [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($webhookLog) {
                $this->updateWebhookLog($webhookLog, 'error', $startTime, ['error' => 'Webhook processing failed'], 500, null, $e->getMessage());
            }

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    private function handleSuccessfulPayment'''

    content = content[:match.start()] + generic + content[match.end():]
    content = content.replace(
        "Log::info('Document marked as paid via EasyPay webhook', [",
        "Log::info('Document marked as paid via payment webhook', [",
        1,
    )
    content = content.replace(
        "Log::info('EasyPay payment failed via webhook', [",
        "Log::info('Payment failed via payment webhook', [",
        1,
    )
    sensitive_old = "$sensitiveHeaders = ['authorization', 'x-easypay-signature', 'cookie'];"
    sensitive_new = "$sensitiveHeaders = ['authorization', 'x-easypay-signature', 'x-authenticity-token', 'cookie'];"
    content = replace_once(content, sensitive_old, sensitive_new, rel)
    changes[rel] = content

    # docs/features/payments.md
    rel = "docs/features/payments.md"
    content = read(rel)
    if "## 3. PagBank Integration Guide" not in content:
        anchor = "## 3. Webhook Implementation Details"
        if anchor not in content:
            fail(f"Expected documentation section not found in {rel}.")
        section = r'''## 3. PagBank Integration Guide

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

## 4. Webhook Implementation Details'''
        content = replace_once(content, anchor, section, rel)
        changes[rel] = content

    return changes


def validate_new_files() -> list[tuple[Path, Path]]:
    pairs: list[tuple[Path, Path]] = []
    for src in SOURCE.rglob("*"):
        if not src.is_file():
            continue
        rel = src.relative_to(SOURCE)
        dst = REPO / rel
        if dst.exists():
            fail(f"Patch would overwrite an existing file: {rel}")
        pairs.append((src, dst))
    return pairs


def main() -> None:
    ensure_git_repo()
    changes = build_modifications()
    new_files = validate_new_files()

    # All validation has completed before any repository file is changed.
    for rel, content in changes.items():
        write(rel, content)

    for src, dst in new_files:
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)

    subprocess.run(["git", "diff", "--check"], cwd=REPO, check=True)

    print("PagBank patch applied successfully.")
    print()
    print("Review with:")
    print("  git status")
    print("  git --no-pager diff --stat")
    print("  git --no-pager diff")
    print()
    print("Then run:")
    print("  vendor/bin/pint --dirty")
    print("  php artisan test --filter=PagBank")
    print("  php artisan route:list | grep -i pagbank")


if __name__ == "__main__":
    main()
