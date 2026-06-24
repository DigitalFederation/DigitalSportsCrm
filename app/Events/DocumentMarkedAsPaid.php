<?php

namespace App\Events;

use Domain\Documents\Models\Document;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a document has been marked as paid.
 *
 * This event is the trigger for external invoice generation (Moloni).
 * It can be dispatched from:
 * - Payment webhooks (automatic payments) - always creates invoice
 * - Manual payment marking (admin) - creates invoice based on admin choice
 */
class DocumentMarkedAsPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public ?PaymentTransaction $transaction,
        public bool $createMoloniInvoice = true,
        public string $source = 'webhook',
        public array $webhookData = []
    ) {}

    /**
     * Get the payment amount from the transaction or document.
     */
    public function getAmount(): float
    {
        if ($this->transaction) {
            return (float) $this->transaction->amount;
        }

        return (float) $this->document->total_value;
    }

    /**
     * Check if this is a manual payment.
     */
    public function isManualPayment(): bool
    {
        return $this->source === 'manual';
    }
}
