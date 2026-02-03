<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Enable PWA
    |--------------------------------------------------------------------------
    | Master switch for the entire PWA package. When disabled, routes are not
    | registered and Blade directives render nothing.
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Web App Manifest
    |--------------------------------------------------------------------------
    */
    'manifest' => [
        'name' => env('APP_NAME', 'My App'),
        'short_name' => env('APP_NAME', 'App'),
        'description' => null,
        'start_url' => '/',
        'display' => 'standalone',
        'orientation' => 'any',
        'theme_color' => '#ffffff',
        'background_color' => '#ffffff',
        'lang' => 'en',
        'dir' => 'ltr',
        'categories' => [],
        'scope' => '/',
        'icons' => [
            ['src' => '/icons/icon-72x72.png', 'sizes' => '72x72', 'type' => 'image/png'],
            ['src' => '/icons/icon-96x96.png', 'sizes' => '96x96', 'type' => 'image/png'],
            ['src' => '/icons/icon-128x128.png', 'sizes' => '128x128', 'type' => 'image/png'],
            ['src' => '/icons/icon-144x144.png', 'sizes' => '144x144', 'type' => 'image/png'],
            ['src' => '/icons/icon-152x152.png', 'sizes' => '152x152', 'type' => 'image/png'],
            ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/icons/icon-384x384.png', 'sizes' => '384x384', 'type' => 'image/png'],
            ['src' => '/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ],
        'screenshots' => [],
        'shortcuts' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Worker
    |--------------------------------------------------------------------------
    */
    'service_worker' => [
        'cache_version' => 'v1',
        'precache_vite_assets' => true,
        'offline_page' => '/offline',
        'cache_strategies' => [
            // URL pattern => strategy
            '/icons/*' => 'CacheFirst',
            '/build/*' => 'CacheFirst',
            '/api/*' => 'NetworkFirst',
            '/*' => 'NetworkFirst',
        ],
        'exclude_patterns' => [
            '/horizon/*',
            '/telescope/*',
            '/admin/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notifications
    |--------------------------------------------------------------------------
    */
    'push' => [
        'enabled' => true,
        'vapid' => [
            'subject' => env('VAPID_SUBJECT', env('APP_URL', 'https://localhost')),
            'public_key' => env('VAPID_PUBLIC_KEY', ''),
            'private_key' => env('VAPID_PRIVATE_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Background Sync
    |--------------------------------------------------------------------------
    */
    'background_sync' => [
        'enabled' => true,
        'retry_endpoint' => '/api/pwa/sync',
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon Generation
    |--------------------------------------------------------------------------
    */
    'icons' => [
        'source' => public_path('icon.png'),
        'output_dir' => public_path('icons'),
        'sizes' => [72, 96, 128, 144, 152, 192, 384, 512],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'enabled' => true,
        'prefix' => 'admin/pwa',
        'middleware' => ['web', 'auth', 'role:super-admin,admin'],
    ],
];
