<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */

    'api' => [
        'version'        => env('API_VERSION', 'v1'),
        'default_locale' => env('APP_LOCALE', 'en'),
        'per_page'       => (int) env('API_PER_PAGE', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'global'    => (int) env('RATE_LIMIT_GLOBAL', 60),    // requests per minute
        'auth'      => (int) env('RATE_LIMIT_AUTH', 10),
        'otp'       => (int) env('RATE_LIMIT_OTP', 5),
        'webhooks'  => (int) env('RATE_LIMIT_WEBHOOKS', 100),
    ],

];
