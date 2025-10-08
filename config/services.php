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

    /*
    |--------------------------------------------------------------------------
    | Trading API Services
    |--------------------------------------------------------------------------
    |
    | Configuration for external trading APIs including Binance and BingX
    |
    */

    'binance' => [
        'base_url' => env('BINANCE_BASE_URL', 'https://api.binance.com'),
        'api_key' => env('BINANCE_API_KEY'),
        'secret_key' => env('BINANCE_SECRET_KEY'),
        'testnet' => env('BINANCE_TESTNET', false),
    ],

    'bingx' => [
        'base_url' => env('BINGX_BASE_URL', 'https://open-api.bingx.com'),
        'api_key' => env('BINGX_API_KEY'),
        'secret_key' => env('BINGX_SECRET_KEY'),
        'testnet' => env('BINGX_TESTNET', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trading Configuration
    |--------------------------------------------------------------------------
    |
    | General trading configuration settings
    |
    */

    'trading' => [
        'default_risk_per_trade' => env('TRADING_DEFAULT_RISK', 2), // 2%
        'max_risk_per_trade' => env('TRADING_MAX_RISK', 5), // 5%
        'min_trade_amount' => env('TRADING_MIN_AMOUNT', 10), // $10
        'max_trade_amount' => env('TRADING_MAX_AMOUNT', 10000), // $10,000
        'default_stop_loss' => env('TRADING_DEFAULT_STOP_LOSS', 5), // 5%
        'default_take_profit' => env('TRADING_DEFAULT_TAKE_PROFIT', 10), // 10%
        'commission_rate' => env('TRADING_COMMISSION_RATE', 0.1), // 0.1%
        'admin_profit_share' => env('TRADING_ADMIN_PROFIT_SHARE', 50), // 50%
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Services
    |--------------------------------------------------------------------------
    |
    | Configuration for notification services
    |
    */

    'notifications' => [
        'email_enabled' => env('NOTIFICATIONS_EMAIL_ENABLED', true),
        'sms_enabled' => env('NOTIFICATIONS_SMS_ENABLED', false),
        'push_enabled' => env('NOTIFICATIONS_PUSH_ENABLED', true),
        'webhook_url' => env('NOTIFICATIONS_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration
    |
    */

    'security' => [
        'max_login_attempts' => env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('SECURITY_LOCKOUT_DURATION', 15), // minutes
        'session_timeout' => env('SECURITY_SESSION_TIMEOUT', 120), // minutes
        'require_2fa' => env('SECURITY_REQUIRE_2FA', false),
        'api_rate_limit' => env('SECURITY_API_RATE_LIMIT', 100), // requests per minute
    ],

];