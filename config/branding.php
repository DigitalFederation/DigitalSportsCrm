<?php

return [
    'primary' => [
        'name' => env('FEDERATION_NAME', 'Example Federation'),
        'short_name' => env('FEDERATION_SHORT_NAME', 'DF'),
        'portal_name' => env('FEDERATION_PORTAL_NAME', env('APP_NAME', 'Digital Sports CRM')),
        'description' => env('FEDERATION_DESCRIPTION', 'Open-source federation management platform.'),
        'about' => env('FEDERATION_ABOUT', 'This portal helps a federation manage members, entities, certifications, licenses, events, payments, and public directories.'),
        'country' => env('FEDERATION_COUNTRY', 'Example Country'),
        'website_url' => env('FEDERATION_WEBSITE_URL', 'https://example.test'),
        'website_label' => env('FEDERATION_WEBSITE_LABEL', 'example.test'),
        'email' => env('FEDERATION_EMAIL', 'contact@example.test'),
        'support_email' => env('FEDERATION_SUPPORT_EMAIL', env('FEDERATION_EMAIL', 'contact@example.test')),
        'address' => env('FEDERATION_ADDRESS', 'Example Street 1, 0000-000 Example City'),
        'phone' => env('FEDERATION_PHONE', '+000 000 000 000'),
        'mobile' => env('FEDERATION_MOBILE', '+000 000 000 000'),
        // Optional: path (relative to public/) to the federation's logo.
        // When empty, the federation name is written as text instead.
        'logo_path' => env('FEDERATION_LOGO_PATH'),
    ],

    'international' => [
        'name' => env('INTERNATIONAL_FEDERATION_NAME', 'International Federation'),
        'short_name' => env('INTERNATIONAL_FEDERATION_SHORT_NAME', 'IF'),
        'country' => env('INTERNATIONAL_FEDERATION_COUNTRY', 'Example Country'),
        'code' => env('INTERNATIONAL_FEDERATION_CODE', 'INTF00'),
        'website_url' => env('INTERNATIONAL_FEDERATION_WEBSITE_URL', 'https://international.example.test'),
        'website_label' => env('INTERNATIONAL_FEDERATION_WEBSITE_LABEL', 'international.example.test'),
        'email' => env('INTERNATIONAL_FEDERATION_EMAIL', 'international@example.test'),
        'address' => env('INTERNATIONAL_FEDERATION_ADDRESS', 'Example International Address'),
        'postal' => env('INTERNATIONAL_FEDERATION_POSTAL', '0000-000 Example City'),
        'logo_path' => env('INTERNATIONAL_FEDERATION_LOGO_PATH', 'img/international-logo.svg'),
        'secondary_logo_path' => env('INTERNATIONAL_FEDERATION_SECONDARY_LOGO_PATH', 'img/international-logo.svg'),
    ],

    'payment' => [
        'descriptor_prefix' => env('PAYMENT_DESCRIPTOR_PREFIX', env('FEDERATION_SHORT_NAME', 'DF')),
        'fallback_email' => env('PAYMENT_FALLBACK_EMAIL', env('FEDERATION_EMAIL', 'billing@example.test')),
    ],
];
