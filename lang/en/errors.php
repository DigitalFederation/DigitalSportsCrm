<?php

return [
    'actions' => [
        'go_back' => 'Go back',
        'go_home' => 'Go to homepage',
        'try_again' => 'Try again',
        'sign_in' => 'Sign in',
    ],

    'not_found' => [
        'title' => "We couldn't find that page",
        'description' => 'The page may have moved or the link is incorrect.',
    ],

    'forbidden' => [
        'title' => 'You don’t have permission to view this page',
        'description' => 'If you think you should have access, please contact an administrator.',
    ],

    'server_error' => [
        'title' => 'Something went wrong',
        'description' => 'It’s not you — it’s us. Please try again in a moment.',
    ],

    'unauthorized' => [
        'title' => "You're not signed in",
        'description' => 'Please sign in to continue.',
    ],

    'payment_required' => [
        'title' => 'Payment required',
        'description' => 'Access requires a valid payment or subscription.',
    ],

    'method_not_allowed' => [
        'title' => 'That action isn’t available',
        'description' => 'The requested method isn’t supported here.',
    ],

    'page_expired' => [
        'title' => 'Your session has expired',
        'description' => 'Please refresh the page and try again.',
    ],

    'too_many_requests' => [
        'title' => 'Too many requests',
        'description' => 'You’re sending requests too quickly. Please wait a moment.',
    ],

    'service_unavailable' => [
        'title' => 'We’re doing some maintenance',
        'description' => 'The service is temporarily unavailable. Please try again shortly.',
    ],
];
