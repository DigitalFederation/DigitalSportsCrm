<?php

return [
    // Payment method names
    'method_offline' => 'Bank Transfer',
    'method_easypay' => 'Multibanco, MB WAY, ...',

    // Payment flow messages
    'offline_payment_instructions' => 'Please make the payment via bank transfer and send the receipt by email or contact the administrative services.',
    'payment_successful' => 'Payment completed successfully.',
    'payment_failed' => 'Payment failed. Please try again.',
    'payment_pending' => 'Payment is being processed. You will be notified when it completes.',

    // Gateway messages
    'easypay_redirect_message' => 'You will be redirected to complete your payment.',
    'payment_method_disabled' => 'The selected payment method is currently disabled.',

    // Error messages
    'invalid_payment_method' => 'Invalid payment method selected.',
    'payment_processing_error' => 'An error occurred while processing your payment.',
    'webhook_signature_invalid' => 'Invalid webhook signature.',

    // Status updates
    'mark_as_paid' => 'Mark as Paid',

    // Checkout page
    'complete_payment' => 'Complete Payment',
    'document' => 'Document',
    'loading_checkout' => 'Loading payment form...',
    'cancel_and_return' => 'Cancel and return to document',
    'powered_by_easypay' => 'Secure payment powered by EasyPay',
    'checkout_error' => 'Failed to load payment form. Please try again.',
    'return_to_document' => 'Return to document',
    'transaction_not_found' => 'Transaction not found or already processed.',
    'invalid_checkout_data' => 'Invalid checkout data. Please start the payment process again.',
    'checkout_expired' => 'The checkout session has expired. Please try again.',
];
