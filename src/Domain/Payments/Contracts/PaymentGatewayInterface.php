<?php

namespace Domain\Payments\Contracts;

use Domain\Documents\Models\Document;
use Domain\Payments\DataTransferObject\PaymentResponseData;

interface PaymentGatewayInterface
{
    /**
     * Initialize the payment gateway with configuration
     */
    public function configure(array $config): void;

    /**
     * Create a payment request for the given document
     * Returns payment response data with redirect URL or other payment details
     */
    public function createPayment(Document $document): PaymentResponseData;

    /**
     * Verify a payment from webhook/callback data
     * Returns payment response data with status and transaction details
     */
    public function verifyPayment(array $webhookData): PaymentResponseData;

    /**
     * Get the gateway name/identifier
     */
    public function getName(): string;

    /**
     * Check if the gateway supports webhooks
     */
    public function supportsWebhooks(): bool;

    /**
     * Get webhook URL for this gateway
     */
    public function getWebhookUrl(): ?string;

    /**
     * Validate webhook signature/authenticity
     */
    public function validateWebhookSignature(array $headers, string $payload): bool;
}
