<?php
// config/payment.php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */
    'default' => env('PAYMENT_DEFAULT', 'nmb'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'nmb' => [
            'name' => 'NMB Bank',
            'type' => 'bank',
            'base_url' => env('NMB_BASE_URL', 'https://obp-api-sandbox.nmbbank.co.tz'),
            'consumer_key' => env('NMB_CONSUMER_KEY'),
            'consumer_secret' => env('NMB_CONSUMER_SECRET'),
            'sandbox' => env('NMB_SANDBOX', true),
            'timeout' => env('NMB_TIMEOUT', 30),
            'retry_attempts' => env('NMB_RETRY_ATTEMPTS', 3),
            'endpoints' => [
                'direct_login' => '/my/logins/direct',
                'oauth_initiate' => '/oauth/initiate',
                'control_number' => '/v1/control-numbers',
                'payment_status' => '/v1/payments/status',
                'mobile_payment' => '/v1/mobile/payments',
            ],
        ],

        'crdb' => [
            'name' => 'CRDB Bank',
            'type' => 'bank',
            'base_url' => env('CRDB_BASE_URL'),
            'consumer_key' => env('CRDB_CONSUMER_KEY'),
            'consumer_secret' => env('CRDB_CONSUMER_SECRET'),
            'sandbox' => env('CRDB_SANDBOX', true),
            'timeout' => 30,
            'enabled' => false, // Disabled until configured
        ],

        'mpesa' => [
    'name' => 'M-Pesa',
    'type' => 'mobile_money',
    'base_url' => env('MPESA_ENVIRONMENT') === 'production' 
        ? 'https://api.safaricom.co.ke' 
        : 'https://sandbox.safaricom.co.ke',
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE', '174379'),
    'passkey' => env('MPESA_PASSKEY'),
    'sandbox' => env('MPESA_ENVIRONMENT') === 'sandbox',
    'timeout' => 30,
    'endpoints' => [
        'oauth' => '/oauth/v1/generate',
        'stk_push' => '/mpesa/stkpush/v1/processrequest',
        'query' => '/mpesa/stkpushquery/v1/query',
    ],
],

        'tigo' => [
            'name' => 'Tigo Pesa',
            'type' => 'mobile_money',
            'base_url' => env('TIGO_BASE_URL'),
            'enabled' => false,
        ],

        'airtel' => [
            'name' => 'Airtel Money',
            'type' => 'mobile_money',
            'base_url' => env('AIRTEL_BASE_URL'),
            'enabled' => false,
        ],

        'halopesa' => [
            'name' => 'Halopesa',
            'type' => 'mobile_money',
            'base_url' => env('HALOPESA_BASE_URL'),
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'control_number_length' => 12,
        'control_number_expiry_days' => 30,
        'min_payment_amount' => 1000, // TZS
        'max_retry_attempts' => 3,
        'webhook_signature_header' => 'X-NMB-Signature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => 'TZS',
    'currency_symbol' => 'TSh',
    'currency_position' => 'left', // left or right

    /*
    |--------------------------------------------------------------------------
    | Receipt Format
    |--------------------------------------------------------------------------
    */
    'receipt_prefix' => 'RCT',
    'receipt_length' => 8,
];