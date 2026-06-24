<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Invoice Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default invoice provider that will be used
    | to generate external invoices for paid documents.
    |
    */

    'default' => env('INVOICE_PROVIDER', 'moloni'),

    /*
    |--------------------------------------------------------------------------
    | Invoice Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each invoice provider.
    | Currently supported: "moloni"
    |
    */

    'providers' => [

        'moloni' => [
            'enabled' => env('MOLONI_ENABLED', false),
            'client_id' => env('MOLONI_CLIENT_ID'),
            'client_secret' => env('MOLONI_CLIENT_SECRET'),
            'base_url' => 'https://api.moloni.pt/v1/',
            'redirect_uri' => env('APP_URL') . '/admin/moloni-settings/callback',
            'timeout' => 30,

            // Alert email for invoice generation failures
            'alert_email' => env('MOLONI_ALERT_EMAIL'),

            // Reconciliation settings
            'reconciliation' => [
                'enabled' => env('MOLONI_RECONCILIATION_ENABLED', false),
                'hours_lookback' => env('MOLONI_RECONCILIATION_HOURS', 72),
                'batch_size' => env('MOLONI_RECONCILIATION_BATCH', 50),
            ],
        ],

    ],

];
