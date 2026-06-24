<?php

return [
    'default' => 'offline',

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Each gateway points its `gateway` key at a class implementing
    | Domain\Payments\Contracts\PaymentGatewayInterface. The core ships the
    | `offline` gateway (the default) and a bundled `easypay` example. To add
    | your own provider, implement the interface and register it here — see
    | docs/guides/building-integrations.md.
    |
    | `easypay` is a Portugal-specific reference integration and is disabled
    | unless its EASYPAY_* environment variables are configured.
    |
    */

    'gateways' => [
        'offline' => [
            'driver' => 'Offline',
            'gateway' => Domain\Payments\Gateways\OfflineGateway::class,
            'handler' => Domain\Payments\Handlers\OfflinePaymentHandler::class,
            'instructions' => null,
        ],
        'easypay' => [
            'driver' => 'EasyPay',
            // Portugal-specific reference gateway: disabled unless explicitly enabled.
            'enabled' => env('EASYPAY_ENABLED', false),
            'gateway' => Domain\Payments\Gateways\EasyPayGateway::class,
            'handler' => Domain\Payments\Handlers\EasyPayPaymentHandler::class,
            'account_id' => env('EASYPAY_ACCOUNT_ID'),
            'api_key' => env('EASYPAY_API_KEY'),
            'webhook_secret' => env('EASYPAY_WEBHOOK_SECRET'),
            'sandbox' => env('EASYPAY_SANDBOX', true),
        ],
    ],

];
