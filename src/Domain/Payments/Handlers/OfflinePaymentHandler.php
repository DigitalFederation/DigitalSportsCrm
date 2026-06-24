<?php

namespace Domain\Payments\Handlers;

use Domain\Documents\Models\Document;

class OfflinePaymentHandler extends BasePaymentHandler
{
    public function pay(Document $document): mixed
    {
        // For offline methods, you may just want to validate the amount, log the request, etc.
        // For this example, we'll assume the payment is always successful.

        return true;
    }
}
