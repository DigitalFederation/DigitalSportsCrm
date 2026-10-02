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
        'pagbank' => [
            'driver' => 'PagBank',
            // Brazil-specific Orders API / PIX gateway: disabled unless explicitly enabled.
            'enabled' => env('PAGBANK_ENABLED', false),
            'gateway' => Domain\Payments\Gateways\PagBankGateway::class,
            'handler' => Domain\Payments\Handlers\PagBankPaymentHandler::class,
            'token' => env('PAGBANK_TOKEN'),
            'sandbox' => env('PAGBANK_SANDBOX', true),
            'allow_unsigned_sandbox_webhooks' => env('PAGBANK_ALLOW_UNSIGNED_SANDBOX_WEBHOOKS', false),
            'pix_expiration_minutes' => (int) env('PAGBANK_PIX_EXPIRATION_MINUTES', 30),
            'payment_page_ttl_minutes' => (int) env('PAGBANK_PAYMENT_PAGE_TTL_MINUTES', 60),
        ],
    ],
];
