<?php

namespace Domain\Payments\Actions;

use Domain\Documents\Models\Document;
use Domain\Payments\Handlers\BasePaymentHandler;
use Domain\Payments\Models\PaymentMethod;

class InitiatePaymentAction
{
    public function execute(Document $document, int $methodId): mixed
    {
        // Get the PaymentMethod model related to this document
        $paymentMethod = PaymentMethod::findOrFail($methodId);

        // Override the method_id in the document if it's different
        if ($document->method_id !== $methodId) {
            $document->method_id = $methodId;
            $document->save();
        }

        if (! $paymentMethod->is_enabled) {
            throw new \Exception('Payment method is currently disabled.');
        }

        $documentCurrency = $document->currency ?? config('app.currency', 'EUR');
        $manager = \Domain\Payments\Services\PaymentGatewayManager::createFromConfig();
        if (! $manager->supportsCurrency($paymentMethod->driver, $documentCurrency)) {
            throw new \Exception("Payment method {$paymentMethod->driver} does not support {$documentCurrency}.");
        }

        // Get the handler instance for this payment method
        $handler = $this->getHandlerInstance($paymentMethod->driver, $document);

        // Use the handler to initiate the payment
        // This can now return different types: bool, RedirectResponse, etc.
        return $handler->pay($document);
    }

    private function getHandlerInstance(string $driver, Document $document): BasePaymentHandler
    {

        // Check configuration
        if (is_null(config("payment.gateways.{$driver}"))) {
            throw new \InvalidArgumentException("Payment gateway {$driver} is not configured.");
        }

        $handlerClass = config("payment.gateways.{$driver}.handler");

        if (! class_exists($handlerClass)) {
            throw new \InvalidArgumentException("Handler class {$handlerClass} does not exist.");
        }

        return new $handlerClass($document);
    }
}
