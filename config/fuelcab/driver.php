<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Driver Matching
    |--------------------------------------------------------------------------
    */

    'matching' => [
        'search_radius_km'       => (float) env('DRIVER_SEARCH_RADIUS_KM', 10.0),
        'max_assign_attempts'    => (int) env('DRIVER_MAX_ASSIGN_ATTEMPTS', 5),
        'assignment_timeout_sec' => (int) env('DRIVER_ASSIGN_TIMEOUT', 30),
        'algo'                   => env('DRIVER_MATCHING_ALGO', 'nearest'), // nearest | weighted
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'expiry_minutes' => (int) env('DRIVER_OTP_EXPIRY', 10),
        'length'         => (int) env('DRIVER_OTP_LENGTH', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

    'required_documents' => [
        'license',
        'rc_book',
        'insurance',
        'aadhar',
        'pan',
    ],

];
