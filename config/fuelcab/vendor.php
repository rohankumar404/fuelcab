<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Vendor Settings
    |--------------------------------------------------------------------------
    */

    'commission_percent'   => (float) env('VENDOR_COMMISSION_PCT', 10.0),
    'settlement_days'      => (int) env('VENDOR_SETTLEMENT_DAYS', 7),
    'max_service_radius_km'=> (float) env('VENDOR_MAX_RADIUS_KM', 50.0),

    /*
    |--------------------------------------------------------------------------
    | Vendor Onboarding
    |--------------------------------------------------------------------------
    */

    'required_documents' => [
        'business_registration',
        'gst_certificate',
        'bank_details',
        'pan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags (per-vendor overridable)
    |--------------------------------------------------------------------------
    */

    'features' => [
        'wallet_enabled'       => true,
        'surge_pricing'        => false,
        'scheduled_orders'     => true,
        'subscription_plans'   => false,
    ],

];
