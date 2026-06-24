<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Fuel Types
    |--------------------------------------------------------------------------
    */

    'types' => [
        'petrol',
        'diesel',
        'cng',
        'lpg',
        'ev',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Rules
    |--------------------------------------------------------------------------
    | base_price: Price per litre (or unit for EV)
    | platform_fee_percent: FuelCab's cut per order
    | gst_percent: Tax applied to total
    */

    'pricing' => [
        'platform_fee_percent' => (float) env('FUEL_PLATFORM_FEE', 5.0),
        'gst_percent'          => (float) env('FUEL_GST_PERCENT', 18.0),
        'min_order_litres'     => (float) env('FUEL_MIN_LITRES', 5.0),
        'max_order_litres'     => (float) env('FUEL_MAX_LITRES', 200.0),
        'surge_multiplier'     => (float) env('FUEL_SURGE_MULTIPLIER', 1.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Config
    |--------------------------------------------------------------------------
    */

    'delivery' => [
        'max_radius_km'       => (float) env('FUEL_MAX_RADIUS_KM', 25.0),
        'eta_buffer_minutes'  => (int) env('FUEL_ETA_BUFFER', 10),
    ],

];
