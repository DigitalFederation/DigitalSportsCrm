<?php

namespace Domain\Payments\Gateways;

use Domain\Documents\Models\Document;
use Domain\Payments\DataTransferObject\PaymentResponseData;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PagBankGateway extends AbstractPaymentGateway
{
    private const SANDBOX_API_URL = 'https://sandbox.api.pagseguro.com';
    private const PRODUCTION_API_URL = 'https://api.pagseguro.com';

    public function getName(): string
    {
        return 'pagbank';
    }

    public function createPayment(Document $document): PaymentResponseData
    {
        $this->validateConfig(['token']);
        if (
            strtoupper((string) config('currency.code', 'EUR'))
            !== 'BRL'
        ) {
            return PaymentResponseData::failed(
                'PagBank PIX requires CURRENCY_CODE=BRL.'
            );
        }

        $amountInCents = (int) round(((float) $document->total_value) * 100);

        if ($amountInCents <= 0) {
            return PaymentResponseData::failed('PagBank requires a positive payment amount.');
        }

        $taxId = $this->resolveBrazilianTaxId($document);

        if ($taxId === '') {
            return PaymentResponseData::failed(
                'PagBank PIX requires a valid Brazilian CPF or CNPJ (11 or 14 digits).'
            );
        }

        $transaction = $this->createPaymentTransaction($document, 'pending');
        $payload = $this->buildOrderPayload($document, $transaction, $amountInCents, $taxId);

        $orderUrl = $this->apiUrl('/orders');

        $this->logPaymentActivity('Sending PIX order to PagBank', [
            'url' => $orderUrl,
            'sandbox' => (bool) $this->getConfig('sandbox', true),
            'document_id' => (string) $document->id,
            'transaction_id' => (string) $transaction->id,
            'amount_cents' => $amountInCents,
            'webhook_url' => $this->getWebhookUrl(),
        ]);

        try {
            $response = $this->apiRequest()
                ->withHeaders([
                    'x-idempotency-key' => str_replace(
                        '-',
                        '',
                        (string) $transaction->id
                    ),
                ])
                ->post($orderUrl, $payload);
        } catch (ConnectionException $e) {
            $message =
                'Could not connect to PagBank Sandbox: '
                . $e->getMessage();

            $this->updatePaymentTransaction(
                $transaction,
                'failed',
                [
                    'connection_error' => $e->getMessage(),
                    'url' => $orderUrl,
                ],
                $message
            );

            $this->logPaymentActivity(
                'PagBank connection failed',
                [
                    'url' => $orderUrl,
                    'transaction_id' => (string) $transaction->id,
                    'error' => $e->getMessage(),
                ]
            );

            return PaymentResponseData::failed(
                $message,
                (string) $transaction->id
            );
        }

        $this->logPaymentActivity(
            'PagBank order response received',
            [
                'transaction_id' => (string) $transaction->id,
                'http_status' => $response->status(),
                'successful' => $response->successful(),
            ]
        );
        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response);
            $this->updatePaymentTransaction(
                $transaction,
                'failed',
                ['pagbank_error' => $response->json()],
                $message
            );

            return PaymentResponseData::failed($message, (string) $transaction->id, $response->json());
        }

        $order = $response->json();
        $charge = $this->extractCharge($order);
        $chargeId = $charge['id'] ?? null;
        $orderId = $order['id'] ?? null;

        if (! $chargeId || ! $orderId) {
            $message = 'PagBank response did not contain an order and charge identifier.';
            $this->updatePaymentTransaction($transaction, 'failed', $order, $message);

            return PaymentResponseData::failed($message, (string) $transaction->id, $order);
        }

        $metadata = $order;
        $metadata['gateway_reference'] = $chargeId;
        $metadata['order_id'] = $orderId;

        $status = strtoupper((string) ($charge['status'] ?? 'WAITING'));

        if (in_array($status, ['DECLINED', 'CANCELED'], true)) {
            $message = (string) data_get(
                $charge,
                'payment_response.message',
                "PagBank payment {$status}."
            );

            $this->updatePaymentTransaction(
                $transaction,
                'failed',
                $metadata,
                "PagBank Charge: {$chargeId}; Order: {$orderId}; {$message}"
            );

            return PaymentResponseData::failed($message, (string) $transaction->id, $metadata);
        }

        $this->updatePaymentTransaction(
            $transaction,
            'pending',
            $metadata,
            "PagBank Charge: {$chargeId}; Order: {$orderId}"
        );

        $this->logPaymentActivity('PIX order created', [
            'transaction_id' => $transaction->id,
            'order_id' => $orderId,
            'charge_id' => $chargeId,
            'status' => $status,
        ]);

        // Payment completion is deliberately asynchronous. Even if the sandbox
        // returns PAID very quickly, the webhook is the source that marks the
        // document as paid after signature and API verification.
        return PaymentResponseData::pending(
            (string) $transaction->id,
            (string) $chargeId,
            $metadata
        );
    }

    public function verifyPayment(array $webhookData): PaymentResponseData
    {
        $this->validateConfig(['token']);

        $chargeFromWebhook = $this->extractCharge($webhookData);
        $chargeId = $chargeFromWebhook['id'] ?? null;

        if (! is_string($chargeId) || ! str_starts_with($chargeId, 'CHAR_')) {
            return PaymentResponseData::failed('PagBank webhook did not contain a valid charge id.');
        }

        $referenceId = $chargeFromWebhook['reference_id'] ?? null;
        $transaction = null;

        if (is_string($referenceId) && $referenceId !== '') {
            $transaction = PaymentTransaction::find($referenceId);
        }

        $transaction ??= $this->findTransactionByReference($chargeId);

        if (! $transaction) {
            return PaymentResponseData::failed(
                'No local payment transaction matches the PagBank charge.',
                null,
                $webhookData
            );
        }

        // Never trust the webhook payload alone. Query the charge from PagBank
        // before changing the local transaction/document state.
        $response = $this->apiRequest()->get(
            $this->apiUrl('/charges/' . rawurlencode($chargeId))
        );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Could not verify PagBank charge via API: ' . $this->extractErrorMessage($response)
            );
        }

        $charge = $response->json();
        $verifiedReference = $charge['reference_id'] ?? null;

        if ($verifiedReference && (string) $verifiedReference !== (string) $transaction->id) {
            throw new RuntimeException('PagBank charge reference does not match the local transaction.');
        }

        $amountInCents = (int) data_get($charge, 'amount.value', 0);
        $expectedAmountInCents = (int) round(((float) $transaction->amount) * 100);
        $currency = strtoupper((string) data_get($charge, 'amount.currency', ''));

        if ($amountInCents !== $expectedAmountInCents || $currency !== 'BRL') {
            throw new RuntimeException('PagBank charge amount/currency does not match the local transaction.');
        }

        $paymentMethod = strtoupper((string) data_get($charge, 'payment_method.type', ''));

        if ($paymentMethod !== 'PIX') {
            throw new RuntimeException('Unexpected PagBank payment method for this gateway.');
        }

        $status = strtoupper((string) ($charge['status'] ?? 'WAITING'));
        $amount = $amountInCents / 100;
        $metadata = [
            'webhook' => $webhookData,
            'verified_charge' => $charge,
        ];

        return match ($status) {
            'PAID' => PaymentResponseData::success(
                (string) $transaction->id,
                $chargeId,
                $amount,
                'BRL',
                $metadata
            ),
            'DECLINED', 'CANCELED' => PaymentResponseData::failed(
                (string) data_get($charge, 'payment_response.message', "PagBank payment {$status}."),
                (string) $transaction->id,
                $metadata
            ),
            default => PaymentResponseData::pending(
                (string) $transaction->id,
                $chargeId,
                $metadata
            ),
        };
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function getWebhookUrl(): ?string
    {
        /*
        * Use APP_URL explicitly.
        *
        * During local development the browser may be accessing localhost,
        * while PagBank must receive a publicly reachable HTTPS notification
        * URL such as the ngrok address.
        */
        $baseUrl = rtrim(
            (string) config('app.url'),
            '/'
        );

        return $baseUrl
            . '/api/payment/webhook/pagbank';
    }

    public function validateWebhookSignature(
        array $headers,
        string $payload
    ): bool {
        $signature = null;

        foreach ($headers as $name => $values) {
            if (strtolower($name) === 'x-authenticity-token') {
                $signature = is_array($values)
                    ? ($values[0] ?? null)
                    : $values;

                break;
            }
        }

        /*
        * The PagBank Orders Sandbox has been observed sending
        * notifications without x-authenticity-token.
        *
        * Never trust the webhook payload itself in this mode.
        * verifyPayment() must re-query GET /charges/{id} using
        * our authenticated server-to-server connection before
        * accepting PAID.
        */
        if (! filled($signature)) {
            return (bool) $this->getConfig('sandbox', false)
                && (bool) $this->getConfig(
                    'allow_unsigned_sandbox_webhooks',
                    false
                );
        }

        $token = trim(
            (string) $this->getConfig('token')
        );

        if ($token === '') {
            return false;
        }

        $expected = hash(
            'sha256',
            $token . '-' . $payload
        );

        return hash_equals(
            strtolower($expected),
            strtolower(trim((string) $signature))
        );
    }

    private function buildOrderPayload(
        Document $document,
        PaymentTransaction $transaction,
        int $amountInCents,
        string $taxId
    ): array {
        $customer = [
            'name' => Str::limit(trim($document->getOrganizationName()) ?: 'Customer', 120, ''),
            'tax_id' => $taxId,
        ];

        $email = auth()->user()?->email
            ?: config('branding.payment.fallback_email')
            ?: config('mail.from.address');

        if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $customer['email'] = $email;
        }

        $description = $document->getDisplayName() ?: 'Document payment';
        $description = Str::limit((string) $description, 64, '');

        return [
            'reference_id' => (string) $transaction->id,
            'customer' => $customer,
            'items' => [
                [
                    'reference_id' => (string) $document->id,
                    'name' => Str::limit($description, 100, ''),
                    'quantity' => 1,
                    'unit_amount' => $amountInCents,
                ],
            ],
            'charges' => [
                [
                    'reference_id' => (string) $transaction->id,
                    'description' => $description,
                    'amount' => [
                        'value' => $amountInCents,
                        'currency' => 'BRL',
                    ],
                    'payment_method' => [
                        'type' => 'PIX',
                        'pix' => [
                            'expiration_date' => now()
                                ->utc()
                                ->addMinutes((int) $this->getConfig('pix_expiration_minutes', 30))
                                ->format('Y-m-d\TH:i:s\Z'),
                        ],
                    ],
                ],
            ],
            'notification_urls' => [
                $this->getWebhookUrl(),
            ],
        ];
    }

    private function extractCharge(array $data): array
    {
        if (isset($data['id']) && is_string($data['id']) && str_starts_with($data['id'], 'CHAR_')) {
            return $data;
        }

        $charge = data_get($data, 'charges.0', []);

        return is_array($charge) ? $charge : [];
    }

    private function apiRequest(): PendingRequest
    {
        return Http::withToken(
            trim((string) $this->getConfig('token'))
        )
            ->withHeaders([
                'Accept' => '*/*',
            ])
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function apiUrl(string $path): string
    {
        $baseUrl = (bool) $this->getConfig('sandbox', true)
            ? self::SANDBOX_API_URL
            : self::PRODUCTION_API_URL;

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function extractErrorMessage(Response $response): string
    {
        $json = $response->json();

        if (is_array($json)) {
            $message = data_get($json, 'error_messages.0.description')
                ?? data_get($json, 'errors.0.description')
                ?? data_get($json, 'errors.0.message')
                ?? data_get($json, 'message');

            if (is_string($message) && $message !== '') {
                return "PagBank API error ({$response->status()}): {$message}";
            }
        }

        return "PagBank API error ({$response->status()}).";
    }

    private function resolveBrazilianTaxId(Document $document): string
    {
        $detail = $document->details->first();

        $candidates = [
            $document->getVatNumber(),
            $document->tax_number,
            $detail?->customer_taxpayer_number,
        ];

        foreach ($candidates as $candidate) {
            $digits = $this->digitsOnly(is_scalar($candidate) ? (string) $candidate : null);

            if (in_array(strlen($digits), [11, 14], true)) {
                return $digits;
            }
        }

        return '';
    }

    private function digitsOnly(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }
}
