<?php

namespace App\Events;

use Domain\Documents\Models\Document;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a payment has been confirmed via webhook.
 *
 * This event is dispatched AFTER the document has been marked as paid
 * and is the recommended hook point for external integrations like
 * invoice generation APIs.
 *
 * Unlike ActivateAfterPayment (which activates subscriptions/licenses),
 * this event provides the raw payment context for external integrations.
 */
class PaymentConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public PaymentTransaction $transaction,
        public array $webhookData,
        public string $gateway
    ) {}

    /**
     * Get the payment amount from the transaction.
     */
    public function getAmount(): float
    {
        return (float) $this->transaction->amount;
    }

    /**
     * Get the gateway reference ID.
     */
    public function getGatewayReference(): ?string
    {
        $paymentData = json_decode($this->transaction->payment_data ?? '{}', true);

        return $paymentData['id'] ?? $paymentData['session']['id'] ?? null;
    }
}
