<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed origins
    |--------------------------------------------------------------------------
    |
    | Use the CORS_ALLOWED_ORIGINS env var to provide a comma-separated list
    | of allowed frontend origins (e.g. https://app.example.com,https://admin.example.com).
    | When allowing credentials (cookies/auth) you MUST provide explicit
    | origins and cannot use '*'.
    |
    */
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports credentials
    |--------------------------------------------------------------------------
    |
    | Set to true if your frontend uses cookies or other credentials (for
    | example when using Sanctum). When true, do NOT set allowed_origins to
    | ['*'] — instead provide explicit origins using CORS_ALLOWED_ORIGINS.
    |
    */
    'supports_credentials' => (bool) env('CORS_ALLOW_CREDENTIALS', false),

];
