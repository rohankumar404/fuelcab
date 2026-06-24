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
    | SMS Provider
    |--------------------------------------------------------------------------
    */

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'), // twilio | msg91 | fast2sms
        'from'     => env('SMS_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Config (shared across Auth + Driver)
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 10),
        'length'         => (int) env('OTP_LENGTH', 6),
        'sandbox'        => (bool) env('OTP_SANDBOX', false), // use static OTP in dev
        'sandbox_code'   => env('OTP_SANDBOX_CODE', '123456'),
    ],

];
