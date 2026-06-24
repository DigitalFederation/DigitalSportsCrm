<?php

namespace Domain\Documents\Actions;

use Domain\Documents\Models\Document;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;

class CheckoutDocumentAction
{
    /**
     * Creates a payment for a document
     *
     * A Payment is defined with the following attributes:
     *
     * - Payment Method
     * - Document Total
     *
     * The method is required to define the properties of the payment.
     * - Offline methods: cash, check, bank transfer
     * - Online methods: credit card, debit card, paypal, etc.
     *
     * This action should return a view to the checkout page with the corresponding payment method.
     */
    public function __invoke(Document $document, PaymentMethod $paymentMethod, RedirectResponse $redirect): RedirectResponse
    {
        // Validate the input and perform any necessary business logic

        // Example: Check if the payment method is an offline payment
        if ($paymentMethod->driver === 'offline') {
            // Perform additional logic for offline payments
            // ...

            return $redirect->route('offline_payment', [$document->id]);
        }

        // Trigger the checkout process for the selected payment method
        // ...

        return $redirect->route('checkout_success');
    }
}
