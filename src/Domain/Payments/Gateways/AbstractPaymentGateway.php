<?php

namespace Domain\Payments\Gateways;

use Domain\Documents\Models\Document;
use Domain\Payments\Contracts\PaymentGatewayInterface;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected array $config = [];

    public function configure(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Create a payment transaction record
     */
    protected function createPaymentTransaction(
        Document $document,
        string $status = 'pending',
        ?string $gatewayReference = null,
        ?array $paymentData = null
    ): PaymentTransaction {
        return PaymentTransaction::create([
            'id' => Str::uuid(),
            'document_id' => $document->id,
            'payment_method_id' => $document->method_id,
            'amount' => $document->total_value,
            'status' => $status,
            'payment_data' => $paymentData ? json_encode($paymentData) : null,
            'comment' => $gatewayReference ? "Gateway Reference: {$gatewayReference}" : null,
        ]);
    }

    /**
     * Update a payment transaction
     */
    protected function updatePaymentTransaction(
        PaymentTransaction $transaction,
        string $status,
        ?array $paymentData = null,
        ?string $comment = null
    ): void {
        $updateData = ['status' => $status];

        if ($paymentData) {
            $updateData['payment_data'] = json_encode($paymentData);
        }

        if ($comment) {
            $updateData['comment'] = $comment;
        }

        $transaction->update($updateData);
    }

    /**
     * Find payment transaction by gateway reference
     */
    protected function findTransactionByReference(string $gatewayReference): ?PaymentTransaction
    {
        return PaymentTransaction::where('comment', 'like', "%{$gatewayReference}%")
            ->orWhereJsonContains('payment_data->gateway_reference', $gatewayReference)
            ->first();
    }

    /**
     * Log payment activity
     */
    protected function logPaymentActivity(string $message, array $context = []): void
    {
        Log::info("[{$this->getName()}] {$message}", $context);
    }

    /**
     * Get configuration value
     */
    protected function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Validate required configuration keys
     */
    protected function validateConfig(array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $this->config) || empty($this->config[$key])) {
                throw new \InvalidArgumentException("Missing required configuration key: {$key}");
            }
        }
    }

    /**
     * Default webhook support (override in child classes)
     */
    public function supportsWebhooks(): bool
    {
        return false;
    }

    /**
     * Default webhook URL (override in child classes)
     */
    public function getWebhookUrl(): ?string
    {
        return null;
    }

    /**
     * Default webhook validation (override in child classes)
     */
    public function validateWebhookSignature(array $headers, string $payload): bool
    {
        return true;
    }
}
