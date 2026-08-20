<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Google Maps API Key
    |--------------------------------------------------------------------------
    */

    'api_key' => env('GOOGLE_MAPS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    'language' => env('GOOGLE_MAPS_LANGUAGE', 'en'),
    'region' => env('GOOGLE_MAPS_REGION', 'IN'),
    'default_radius_km' => (float) env('GOOGLE_MAPS_DEFAULT_RADIUS_KM', 10),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('GOOGLE_MAPS_TIMEOUT', 10),
    'retry_attempts' => (int) env('GOOGLE_MAPS_RETRY', 2),

];
