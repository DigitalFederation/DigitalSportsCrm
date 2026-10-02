<?php

namespace Domain\Payments\Handlers;

use Domain\Documents\Models\Document;
use Domain\Payments\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\URL;

class PagBankPaymentHandler extends BasePaymentHandler
{
    public function pay(Document $document): mixed
    {
        $gatewayManager = PaymentGatewayManager::createFromConfig();
        $gateway = $gatewayManager->gateway('pagbank');

        $response = $gateway->createPayment($document);

        if ($response->isFailed()) {
            throw new \Exception($response->errorMessage ?? 'PagBank payment failed');
        }

        if (! $response->transactionId) {
            throw new \RuntimeException('PagBank did not create a local payment transaction.');
        }

        $ttl = max(
            (int) config('payment.gateways.pagbank.payment_page_ttl_minutes', 60),
            (int) config('payment.gateways.pagbank.pix_expiration_minutes', 30)
        );

        return redirect()->to(URL::temporarySignedRoute(
            'payment.pagbank.show',
            now()->addMinutes($ttl),
            ['transaction' => $response->transactionId]
        ));
    }
}
