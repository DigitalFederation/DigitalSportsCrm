<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | This file contains feature flags for various application features.
    | Feature flags allow for gradual rollout and A/B testing of new features.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Role System Features
    |--------------------------------------------------------------------------
    */
    'use_new_role_system' => env('USE_NEW_ROLE_SYSTEM', false),

    /*
    |--------------------------------------------------------------------------
    | Dynamic Menu System Features
    |--------------------------------------------------------------------------
    */
    'dynamic_menu' => [
        // Global enable/disable for dynamic menu system
        'enabled' => env('DYNAMIC_MENU_ENABLED', false),

        // Per-menu type feature flags for gradual migration
        'menus' => [
            'admin' => env('DYNAMIC_MENU_ADMIN', false),
            'federation' => env('DYNAMIC_MENU_FEDERATION', false),
            'entity' => env('DYNAMIC_MENU_ENTITY', false),
            'individual' => env('DYNAMIC_MENU_INDIVIDUAL', false),
        ],

        // Admin features
        'admin_interface' => env('DYNAMIC_MENU_ADMIN', false),

        // Development features
        'debug_mode' => env('DYNAMIC_MENU_DEBUG', false),
        'cache_enabled' => env('DYNAMIC_MENU_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Other Feature Flags
    |--------------------------------------------------------------------------
    */

    // Add other feature flags here as needed
    'example_feature' => env('EXAMPLE_FEATURE_ENABLED', false),
];
