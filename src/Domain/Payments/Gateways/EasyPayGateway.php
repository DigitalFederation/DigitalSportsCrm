<?php

namespace Domain\Payments\Gateways;

use Domain\Documents\Models\Document;
use Domain\Payments\DataTransferObject\PaymentResponseData;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * EasyPay Payment Gateway Implementation
 *
 * This gateway integrates with EasyPay's API 2.0 for payment processing.
 *
 * IMPORTANT: EasyPay does NOT use webhook signatures for authentication.
 * Instead, security is achieved by querying the EasyPay API to verify
 * that notifications are genuine. See: https://docs.easypay.pt/docs/guides/webhooks
 *
 * Webhook Configuration:
 * - Backoffice: Developers > Configuration API 2.0 > Notifications > Generic URL
 * - Set to: https://app.example.test/api/payment/webhook/easypay
 *
 * @see /docs/easypay_integration.md for full documentation
 */
class EasyPayGateway extends AbstractPaymentGateway
{
    private const SANDBOX_API_URL = 'https://api.test.easypay.pt/2.0';
    private const PRODUCTION_API_URL = 'https://api.prod.easypay.pt/2.0';

    public function getName(): string
    {
        return 'easypay';
    }

    /**
     * EasyPay is a Portuguese (SIBS/Multibanco/MB WAY) gateway — EUR only.
     *
     * @return string[]
     */
    public function supportedCurrencies(): array
    {
        return ['EUR'];
    }

    public function createPayment(Document $document): PaymentResponseData
    {
        $this->validateConfig(['account_id', 'api_key']);

        // Validate amount before making API request
        $amount = (float) $document->total_value;
        if ($amount <= 0) {
            return PaymentResponseData::failed('Invalid payment amount: must be greater than 0');
        }

        try {
            $this->logPaymentActivity('Creating EasyPay payment link', [
                'document_id' => $document->id,
                'amount' => $document->total_value,
            ]);

            // Create payment transaction first
            $transaction = $this->createPaymentTransaction($document, 'pending');

            // Prepare link data
            $linkData = $this->buildLinkData($document, $transaction);

            // Make API request to EasyPay Pay By Link endpoint
            $response = $this->makeApiRequest('POST', '/link', $linkData);

            if ($response->successful()) {
                $responseData = $response->json();

                // Pay By Link API returns: { "id": "uuid", "url": "https://pay.easypay.pt/...", "status": "ACTIVE" }
                $linkId = $responseData['id'] ?? null;
                $paymentUrl = $responseData['url'] ?? null;

                if (! $linkId || ! $paymentUrl) {
                    return PaymentResponseData::failed('Invalid EasyPay response: missing link id or url');
                }

                // Update transaction with EasyPay link ID
                $this->updatePaymentTransaction(
                    $transaction,
                    'pending',
                    $responseData,
                    "EasyPay Link: {$linkId}"
                );

                $this->logPaymentActivity('EasyPay payment link created successfully', [
                    'link_id' => $linkId,
                    'payment_url' => $paymentUrl,
                    'transaction_id' => $transaction->id,
                ]);

                // Redirect to EasyPay payment page
                return PaymentResponseData::redirect(
                    redirectUrl: $paymentUrl,
                    transactionId: $transaction->id,
                    gatewayReference: $linkId,
                    metadata: $responseData
                );
            }

            $responseData = $response->json() ?? [];
            $errorMessage = $this->extractErrorMessage($responseData, $response->status());
            $this->logPaymentActivity('EasyPay link creation failed', [
                'error' => $errorMessage,
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            return PaymentResponseData::failed($errorMessage, $transaction->id);

        } catch (\Exception $e) {
            $this->logPaymentActivity('EasyPay payment creation error', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
            ]);

            return PaymentResponseData::failed($e->getMessage());
        }
    }

    /**
     * Verify payment status from webhook data.
     *
     * EasyPay Generic Notification payload structure:
     * {
     *   "id": "payment-uuid",
     *   "key": "merchant-key",
     *   "type": "capture|authorisation|sale",
     *   "status": "success|failed",
     *   "messages": ["..."],
     *   "date": "2022-08-10 14:56:54"
     * }
     *
     * IMPORTANT: Per EasyPay documentation, we MUST query their API to verify
     * the notification is genuine. This is their recommended security model
     * (they don't use webhook signatures).
     *
     * @see https://docs.easypay.pt/docs/guides/webhooks
     * @see \App\Http\Controllers\Api\PaymentWebhookController
     */
    public function verifyPayment(array $webhookData): PaymentResponseData
    {
        try {
            $this->logPaymentActivity('Processing EasyPay webhook', ['data' => $webhookData]);

            // Extract fields from generic notification
            $paymentId = $webhookData['id'] ?? null;
            $merchantKey = $webhookData['key'] ?? null;
            $notificationType = $webhookData['type'] ?? 'unknown';
            $notificationStatus = strtolower($webhookData['status'] ?? 'unknown');

            // For transaction notifications, the key is nested in transaction.key
            $transactionKey = $webhookData['transaction']['key'] ?? null;

            if (! $paymentId) {
                return PaymentResponseData::failed('Missing payment ID in webhook data');
            }

            // Skip payment_page type notifications - these are page events, not payment confirmations
            // Actual payment webhooks have type: capture, sale, or authorisation
            if ($notificationType === 'payment_page') {
                $this->logPaymentActivity('EasyPay payment_page notification ignored (page event, not payment)', [
                    'payment_id' => $paymentId,
                    'status' => $notificationStatus,
                ]);

                // Return acknowledgment without processing (use constructor to allow null transactionId)
                return new PaymentResponseData(
                    status: 'pending',
                    transactionId: null,
                    gatewayReference: $paymentId,
                    metadata: ['skipped' => true, 'reason' => 'payment_page notifications are page events, not payment confirmations']
                );
            }

            $this->logPaymentActivity('EasyPay notification received', [
                'payment_id' => $paymentId,
                'merchant_key' => $merchantKey,
                'transaction_key' => $transactionKey,
                'type' => $notificationType,
                'status' => $notificationStatus,
            ]);

            // CRITICAL: Query EasyPay API first to verify the notification is genuine
            // This is EasyPay's recommended security model and also helps us find the link ID
            $verifiedPayment = $this->queryPaymentStatus($paymentId);

            // Find the transaction - try multiple strategies:
            // 1. By transaction_key from transaction notifications
            // 2. By merchant key from generic notification
            // 3. By link ID from verified payment response
            // 4. By payment ID in comment or payment_data
            // 5. By amount and pending status (last resort)
            $transaction = null;

            // Try transaction_key first (from transaction notifications)
            if (! empty($transactionKey)) {
                $transaction = PaymentTransaction::find($transactionKey);
                if ($transaction) {
                    $this->logPaymentActivity('Transaction found by transaction_key', [
                        'transaction_id' => $transaction->id,
                    ]);
                }
            }

            // Try merchant key (from generic notifications)
            if (! $transaction && ! empty($merchantKey)) {
                $transaction = PaymentTransaction::find($merchantKey);
                if ($transaction) {
                    $this->logPaymentActivity('Transaction found by merchant key', [
                        'transaction_id' => $transaction->id,
                    ]);
                }
            }

            // Try to find by capture key from verified payment response
            // Handle both Pay By Link structure and Single Payment structure
            if (! $transaction && $verifiedPayment) {
                // Pay By Link structure: payment.capture.key
                $captureKey = $verifiedPayment['payment']['capture']['key'] ?? null;

                // Single Payment structure: direct 'key' field
                if (! $captureKey) {
                    $captureKey = $verifiedPayment['key'] ?? null;
                }

                // Single Payment structure: captures[0].transaction_key
                if (! $captureKey && ! empty($verifiedPayment['captures'])) {
                    $captureKey = $verifiedPayment['captures'][0]['transaction_key'] ?? null;
                }

                if ($captureKey) {
                    $transaction = PaymentTransaction::find($captureKey);
                    if ($transaction) {
                        $this->logPaymentActivity('Transaction found by capture key from API', [
                            'transaction_id' => $transaction->id,
                            'capture_key' => $captureKey,
                        ]);
                    }
                }
            }

            // Try to find by link ID from verified payment response
            if (! $transaction && $verifiedPayment) {
                $linkId = $verifiedPayment['id'] ?? null;
                if ($linkId) {
                    $transaction = $this->findTransactionByReference($linkId);
                    if ($transaction) {
                        $this->logPaymentActivity('Transaction found by link ID from API', [
                            'transaction_id' => $transaction->id,
                            'link_id' => $linkId,
                        ]);
                    }
                }
            }

            // Fallback: try to find by payment ID in comment or payment_data
            if (! $transaction) {
                $transaction = $this->findTransactionByReference($paymentId);
            }

            // Last resort: find pending EasyPay transaction by amount
            if (! $transaction) {
                // API v2.0 uses payment.single.requested_amount
                $amount = $verifiedPayment['payment']['single']['requested_amount']
                    ?? $webhookData['value']
                    ?? $verifiedPayment['value']
                    ?? null;
                if ($amount) {
                    $transaction = $this->findPendingTransactionByAmount((float) $amount);
                    if ($transaction) {
                        $this->logPaymentActivity('Transaction found by amount match', [
                            'transaction_id' => $transaction->id,
                            'amount' => $amount,
                        ]);
                    }
                }
            }

            if (! $transaction) {
                $this->logPaymentActivity('Transaction not found for payment ID', ['payment_id' => $paymentId]);

                return PaymentResponseData::failed('Transaction not found', null, $webhookData);
            }

            if (! $verifiedPayment) {
                $this->logPaymentActivity('Failed to verify payment with EasyPay API', [
                    'payment_id' => $paymentId,
                ]);

                return PaymentResponseData::failed(
                    'Could not verify payment with EasyPay',
                    $transaction->id,
                    $webhookData
                );
            }

            // Use the verified status from API, not the webhook
            // Pay By Link uses: status (ACTIVE, FINALIZED, EXPIRED, DISABLED)
            // Single Payment uses: payment_status (success, failed, pending)
            $verifiedStatus = strtoupper(
                $verifiedPayment['payment_status']
                ?? $verifiedPayment['status']
                ?? 'UNKNOWN'
            );

            $this->logPaymentActivity('EasyPay payment verified via API', [
                'payment_id' => $paymentId,
                'verified_status' => $verifiedStatus,
                'transaction_id' => $transaction->id,
            ]);

            // Extract amount from API response (handle both structures)
            $amount = $verifiedPayment['payment']['single']['requested_amount']
                ?? $verifiedPayment['value']
                ?? $transaction->amount;

            // Determine response based on verified status
            switch ($verifiedStatus) {
                // Pay By Link success status
                case 'FINALIZED':
                    // Single Payment success statuses
                case 'SUCCESS':
                case 'PAID':
                    return PaymentResponseData::success(
                        transactionId: $transaction->id,
                        gatewayReference: $paymentId,
                        amount: (float) $amount,
                        metadata: array_merge($webhookData, ['verified_data' => $verifiedPayment])
                    );

                    // Failure statuses
                case 'EXPIRED':
                case 'DISABLED':
                case 'FAILED':
                case 'CANCELLED':
                    return PaymentResponseData::failed(
                        "Payment {$verifiedStatus}",
                        $transaction->id,
                        array_merge($webhookData, ['verified_data' => $verifiedPayment])
                    );

                    // Pending statuses
                case 'ACTIVE':
                case 'PENDING':
                    return PaymentResponseData::pending(
                        transactionId: $transaction->id,
                        gatewayReference: $paymentId,
                        metadata: array_merge($webhookData, ['verified_data' => $verifiedPayment])
                    );

                default:
                    $this->logPaymentActivity('EasyPay unknown status', [
                        'status' => $verifiedStatus,
                        'payment_id' => $paymentId,
                    ]);

                    return PaymentResponseData::pending(
                        transactionId: $transaction->id,
                        gatewayReference: $paymentId,
                        metadata: array_merge($webhookData, ['verified_data' => $verifiedPayment])
                    );
            }

        } catch (\Exception $e) {
            $this->logPaymentActivity('EasyPay webhook processing error', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);

            return PaymentResponseData::failed($e->getMessage());
        }
    }

    /**
     * Query EasyPay API to verify payment status.
     *
     * This is the recommended security model per EasyPay documentation.
     * We query their API to confirm the notification is genuine and get
     * the authoritative payment status.
     */
    private function queryPaymentStatus(string $paymentId): ?array
    {
        try {
            // Try to get payment details from the single endpoint (Pay By Link uses this)
            $response = $this->makeApiRequest('GET', "/link/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            // If not found as link, try single payment endpoint
            $response = $this->makeApiRequest('GET', "/single/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('EasyPay: Could not find payment in API', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('EasyPay: API query failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find a pending EasyPay transaction by amount as a last resort.
     * Only matches transactions created within the last 30 days.
     */
    private function findPendingTransactionByAmount(float $amount): ?PaymentTransaction
    {
        return PaymentTransaction::where('payment_method', 'easypay')
            ->where('status', 'pending')
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function getWebhookUrl(): ?string
    {
        return route('api.payment.webhook.easypay');
    }

    /**
     * Validate webhook signature.
     *
     * IMPORTANT: EasyPay does NOT use webhook signatures for authentication.
     * Their security model relies on querying the EasyPay API to verify
     * that notifications are genuine (done in verifyPayment method).
     *
     * This method always returns true because:
     * 1. EasyPay doesn't send signature headers
     * 2. Security is achieved by API verification in verifyPayment()
     *
     * Optional: You can implement IP whitelisting here for extra security.
     * EasyPay's IPs can be obtained from their documentation or support.
     *
     * @see https://docs.easypay.pt/docs/guides/webhooks
     */
    public function validateWebhookSignature(array $headers, string $payload): bool
    {
        // EasyPay does NOT use webhook signatures
        // Security is achieved by querying their API in verifyPayment()
        // Optionally, you could implement IP whitelisting here

        $this->logPaymentActivity('EasyPay webhook received (no signature validation - per EasyPay docs)', [
            'payload_length' => strlen($payload),
        ]);

        return true;
    }

    /**
     * Build link data for EasyPay Pay By Link API
     *
     * @see https://docs.easypay.pt - Pay By Link documentation
     */
    private function buildLinkData(Document $document, PaymentTransaction $transaction): array
    {
        $customer = $this->getCustomerData($document);

        // Expiration 30 days from now in RFC3339 format
        $expiration = now()->addDays(30)->toRfc3339String();

        // Build a descriptive string for the payment
        $descriptorPrefix = config('branding.payment.descriptor_prefix', config('branding.primary.short_name', 'DF'));
        $description = "{$descriptorPrefix} - {$document->reference}";
        if (! empty($document->customer_name)) {
            $description .= " - {$document->customer_name}";
        }

        return [
            'type' => 'SINGLE',
            'expiration_time' => $expiration,
            'customer' => [
                'name' => Str::limit($customer['name'], 255, ''),
                'email' => $customer['email'],
                'phone' => $customer['phone'] ?? '+351910000000',
                'language' => 'PT',
            ],
            'communication_channels' => [], // We handle communication ourselves
            'payment' => [
                'methods' => ['CC', 'MB', 'MBW', 'AP', 'GP'], // Credit card, Multibanco, MB WAY, Apple Pay, Google Pay
                'capture' => [
                    'descriptive' => Str::limit($description, 255, ''),
                    'key' => $transaction->id, // Our internal reference - echoed back in webhooks
                ],
                'single' => [
                    'requested_amount' => number_format((float) $document->total_value, 2, '.', ''),
                ],
            ],
        ];
    }

    /**
     * Extract customer data from document
     */
    private function getCustomerData(Document $document): array
    {
        // Use getOrganizationName() which properly resolves from owner relationship
        $name = $document->getOrganizationName();

        // Ensure we have a valid name (not empty or 'N/A')
        if (empty($name) || $name === 'N/A') {
            $name = 'Customer';
        }

        // Get email with proper fallback for empty strings
        $email = auth()->user()?->email;
        if (empty($email)) {
            $email = config('branding.payment.fallback_email', 'billing@example.test');
        }

        $customer = [
            'name' => $name,
            'email' => $email,
        ];

        // Add additional customer data if available from document details
        if ($detail = $document->details->first()) {
            if (! empty($detail->customer_taxpayer_number)) {
                $customer['fiscal_number'] = $detail->customer_taxpayer_number;
            }

            if (! empty($detail->customer_phone)) {
                $phone = $this->formatPhoneE164($detail->customer_phone);
                if (! empty($phone)) {
                    $customer['phone'] = $phone;
                }
            }
        }

        return $customer;
    }

    /**
     * Format phone number to E.164 format
     */
    private function formatPhoneE164(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If already starts with +, assume it's E.164
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // If starts with 00, replace with +
        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        // Assume Portuguese number and add country code
        if (strlen($phone) === 9) {
            return '+351' . $phone;
        }

        // Return with + prefix as fallback
        return '+' . $phone;
    }

    /**
     * Extract error message from API response
     */
    private function extractErrorMessage(array $responseData, int $statusCode): string
    {
        // Try to extract error message from various possible fields
        $possibleErrorFields = ['message', 'error', 'error_description', 'detail', 'msg'];

        foreach ($possibleErrorFields as $field) {
            if (isset($responseData[$field])) {
                $value = $responseData[$field];

                if (is_string($value)) {
                    return $value;
                } elseif (is_array($value)) {
                    return implode(', ', array_filter($value, 'is_string'));
                }
            }
        }

        // If no specific error message found, return a generic one based on status code
        return match ($statusCode) {
            400 => 'Bad request - invalid payment data',
            401 => 'Authentication failed - check API credentials',
            403 => 'Access forbidden - insufficient permissions',
            404 => 'EasyPay endpoint not found',
            422 => 'Validation failed - check payment parameters',
            429 => 'Rate limit exceeded - try again later',
            500 => 'EasyPay server error - try again later',
            503 => 'EasyPay service unavailable - try again later',
            default => "EasyPay API request failed with status {$statusCode}",
        };
    }

    /**
     * Make API request to EasyPay
     */
    private function makeApiRequest(string $method, string $endpoint, array $data = []): Response
    {
        $baseUrl = $this->getConfig('sandbox', true)
            ? self::SANDBOX_API_URL
            : self::PRODUCTION_API_URL;

        return Http::withHeaders([
            'AccountId' => $this->getConfig('account_id'),
            'ApiKey' => $this->getConfig('api_key'),
        ])->asJson()->timeout(30)->{strtolower($method)}($baseUrl . $endpoint, $data);
    }
}
