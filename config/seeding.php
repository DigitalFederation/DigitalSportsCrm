<?php

return [
    'default_admin' => [
        'enabled' => env('SEED_DEFAULT_ADMIN', false),
        'name' => env('DEFAULT_ADMIN_NAME', 'admin'),
        'email' => env('DEFAULT_ADMIN_EMAIL', 'admin@example.test'),
        'password' => env('DEFAULT_ADMIN_PASSWORD'),
    ],
];
