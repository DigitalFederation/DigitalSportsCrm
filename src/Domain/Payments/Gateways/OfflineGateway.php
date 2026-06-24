<?php

namespace Domain\Payments\Gateways;

use Domain\Documents\Models\Document;
use Domain\Payments\DataTransferObject\PaymentResponseData;

class OfflineGateway extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'offline';
    }

    public function createPayment(Document $document): PaymentResponseData
    {
        $this->logPaymentActivity('Creating offline payment', [
            'document_id' => $document->id,
            'amount' => $document->total_value,
        ]);

        // Create pending transaction for offline payment
        $transaction = $this->createPaymentTransaction($document, 'pending');

        // For offline payments, we don't redirect but return success
        // The payment will be manually confirmed later
        return PaymentResponseData::pending(
            transactionId: $transaction->id,
            metadata: [
                'payment_method' => 'offline',
                'instructions' => $this->getConfig('instructions', 'Please follow the payment instructions provided.'),
            ]
        );
    }

    public function verifyPayment(array $webhookData): PaymentResponseData
    {
        // Offline payments don't have webhooks
        // Manual verification would be handled through admin interface
        return PaymentResponseData::failed('Offline payments do not support automatic verification');
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function getWebhookUrl(): ?string
    {
        return null;
    }
}
