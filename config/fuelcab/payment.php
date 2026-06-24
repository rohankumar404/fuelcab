<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    */

    'default_gateway' => env('PAYMENT_GATEWAY', 'razorpay'),

    'gateways' => [
        'razorpay' => [
            'key'    => env('RAZORPAY_KEY'),
            'secret' => env('RAZORPAY_SECRET'),
        ],
        'stripe' => [
            'key'    => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Logic
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'max_attempts'    => (int) env('PAYMENT_RETRY_ATTEMPTS', 3),
        'backoff_seconds' => (int) env('PAYMENT_RETRY_BACKOFF', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    */

    'webhook' => [
        'secret'    => env('PAYMENT_WEBHOOK_SECRET'),
        'tolerance' => (int) env('PAYMENT_WEBHOOK_TOLERANCE', 300), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */

    'wallet' => [
        'min_topup'   => (float) env('WALLET_MIN_TOPUP', 100),
        'max_balance' => (float) env('WALLET_MAX_BALANCE', 50000),
    ],

];
