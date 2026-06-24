<?php

namespace Domain\Payments\Handlers;

use Domain\Documents\Models\Document;
use Domain\Payments\Services\PaymentGatewayManager;

class EasyPayPaymentHandler extends BasePaymentHandler
{
    public function pay(Document $document): mixed
    {
        $gatewayManager = PaymentGatewayManager::createFromConfig();
        $gateway = $gatewayManager->gateway('easypay');

        $response = $gateway->createPayment($document);

        if ($response->requiresRedirect()) {
            // Return redirect response for the controller to handle
            return redirect($response->redirectUrl);
        }

        if ($response->isSuccess()) {
            return true;
        }

        if ($response->isFailed()) {
            throw new \Exception($response->errorMessage ?? 'EasyPay payment failed');
        }

        // For pending status, return true to allow the flow to continue
        return true;
    }
}
