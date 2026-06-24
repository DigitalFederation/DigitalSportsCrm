<?php

namespace Domain\Payments\Handlers;

use Domain\Documents\Models\Document;
use Domain\Payments\Models\PaymentTransaction;

abstract class BasePaymentHandler
{
    protected $document;

    protected $paymentTransaction;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->paymentTransaction = new PaymentTransaction;
    }

    abstract public function pay(Document $document): mixed;

    protected function createTransaction(float $amount, string $status)
    {
        $this->paymentTransaction->fill([
            'document_id' => $this->document->id,
            'amount' => $amount,
            'status' => $status,
        ])->save();
    }
}
