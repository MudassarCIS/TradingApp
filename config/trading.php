<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trading Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the embedded trading dashboard iframe.
    | This URL should be HTTPS in production to avoid mixed content issues.
    |
    */

    'dashboard_url' => env('TRADING_DASHBOARD_URL', 'http://165.22.59.174:5173/'),

    /*
    |--------------------------------------------------------------------------
    | Environment-specific URLs
    |--------------------------------------------------------------------------
    |
    | Override dashboard URL based on environment if needed.
    | For local development, HTTP is acceptable.
    | For production, use HTTPS or a reverse proxy.
    |
    */

    'dashboard_urls' => [
        'local' => env('TRADING_DASHBOARD_URL_LOCAL', 'http://165.22.59.174:5173/'),
        'production' => env('TRADING_DASHBOARD_URL_PROD', 'https://trading-dashboard.tsgtrades.com/'),
        'demo' => env('TRADING_DASHBOARD_URL_DEMO', 'https://trading-dashboard.demo.tsgtrades.com/'),
    ],
];

