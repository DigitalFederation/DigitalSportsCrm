<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'scheduler_heartbeat' => [
        'url' => env('SCHEDULER_HEARTBEAT_URL'),
        'timeout' => env('SCHEDULER_HEARTBEAT_TIMEOUT', 5),
    ],

    'scheduler' => [
        'failure_email' => env('SCHEDULER_FAILURE_EMAIL'),
        'summary_email' => env('SCHEDULER_SUMMARY_EMAIL'),
        'summary_send_empty' => env('SCHEDULER_SUMMARY_SEND_EMPTY', false),
    ],

];
