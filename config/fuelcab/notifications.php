<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */

    'channels' => [
        'push'  => env('PUSH_ENABLED', true),
        'sms'   => env('SMS_ENABLED', true),
        'email' => env('EMAIL_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notification (FCM / APNS)
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'sender_id'  => env('FCM_SENDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Provider — Authkey.io
    |--------------------------------------------------------------------------
    | No secret key required. Only authkey + sid are needed.
    | Docs: https://authkey.io/docs
    */

    'authkey' => [
        'key'          => env('AUTHKEY_KEY', ''),
        'sid'          => env('AUTHKEY_SID', ''),
        'country_code' => env('AUTHKEY_COUNTRY_CODE', '91'),
        'base_url'     => 'https://api.authkey.io/request',
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Config (shared across Auth + Driver)
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'expiry_minutes'  => (int) env('OTP_EXPIRY_MINUTES', 10),
        'length'          => (int) env('OTP_LENGTH', 6),
        'max_resend'      => (int) env('OTP_MAX_RESEND', 3),
        'resend_window'   => (int) env('OTP_RESEND_WINDOW_MINUTES', 10),
        'sandbox'         => (bool) env('OTP_SANDBOX', false),
        'sandbox_code'    => env('OTP_SANDBOX_CODE', '123456'),
    ],

];
