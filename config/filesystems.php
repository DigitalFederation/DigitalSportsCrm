<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "scoped"
    |
    */

    'disks' => [

        /*
        |--------------------------------------------------------------------------
        | Private Local Disks (Always Local - Never R2)
        |--------------------------------------------------------------------------
        |
        | These disks store sensitive or temporary files that should never be
        | uploaded to cloud storage.
        |
        */

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'exports' => [
            'driver' => 'local',
            'root' => storage_path('app/exports'),
            'throw' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Secure Media Disk (Private - Controller Access Only)
        |--------------------------------------------------------------------------
        |
        | This disk stores private files that should NEVER be publicly accessible.
        | Files are served through controllers with authorization checks.
        | Uses R2 in production but with PRIVATE visibility (no public URLs).
        |
        */
        'secure-media' => env('FILESYSTEM_PUBLIC_DRIVER', 'local') === 'r2' ? [
            'driver' => 'scoped',
            'disk' => 'r2',
            'prefix' => 'secure',
            'visibility' => 'private',
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/secure-media'),
            'visibility' => 'private',
            'throw' => false,
        ],

        'r2-backups' => env('FILESYSTEM_PUBLIC_DRIVER', 'local') === 'r2' ? [
            'driver' => 'scoped',
            'disk' => 'r2',
            'prefix' => 'backups',
            'visibility' => 'private',
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/backups'),
            'visibility' => 'private',
            'throw' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Cloudflare R2 Base Disk
        |--------------------------------------------------------------------------
        |
        | Base R2 disk configuration. Other disks use this as their foundation
        | via the scoped driver for path prefixing.
        |
        */

        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => 'auto',
            'bucket' => env('R2_BUCKET'),
            'url' => env('R2_URL'),
            'endpoint' => env('R2_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'visibility' => 'public',
            'throw' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Public Disks (Environment Switchable: Local or R2)
        |--------------------------------------------------------------------------
        |
        | These disks can switch between local storage and Cloudflare R2 based on
        | the FILESYSTEM_PUBLIC_DRIVER environment variable.
        |
        | Local (staging): FILESYSTEM_PUBLIC_DRIVER=local (default)
        | Production:      FILESYSTEM_PUBLIC_DRIVER=r2
        |
        */

        'media' => env('FILESYSTEM_PUBLIC_DRIVER', 'local') === 'r2' ? [
            'driver' => 'scoped',
            'disk' => 'r2',
            'prefix' => 'media',
            'visibility' => 'public',
        ] : [
            'driver' => 'local',
            'root' => storage_path('media'),
            'url' => env('APP_URL').'/media',
            'visibility' => 'public',
            'throw' => false,
        ],

        'public' => env('FILESYSTEM_PUBLIC_DRIVER', 'local') === 'r2' ? [
            'driver' => 'scoped',
            'disk' => 'r2',
            'prefix' => 'public',
            'visibility' => 'public',
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'lms-media' => env('FILESYSTEM_PUBLIC_DRIVER', 'local') === 'r2' ? [
            'driver' => 'scoped',
            'disk' => 'r2',
            'prefix' => 'lms-media',
            'visibility' => 'public',
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/public/lms-media'),
            'url' => env('APP_URL').'/storage/lms-media',
            'visibility' => 'public',
            'throw' => false,
        ],

        'attachments' => env('FILESYSTEM_PUBLIC_DRIVER', 'local') === 'r2' ? [
            'driver' => 'scoped',
            'disk' => 'r2',
            'prefix' => 'attachments',
            'visibility' => 'public',
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/public/attachments'),
            'url' => env('APP_URL').'/storage/attachments',
            'visibility' => 'public',
            'throw' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Legacy S3 Disk
        |--------------------------------------------------------------------------
        |
        | Kept for backward compatibility with AWS S3.
        |
        */

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('media') => storage_path('media'),
    ],

];
