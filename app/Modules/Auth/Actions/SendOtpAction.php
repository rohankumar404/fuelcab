<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Events\OtpRequested;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOtpAction
{
    /**
     * Generate, cache, and dispatch a 6-digit OTP to the user's phone.
     */
    public function execute(string $phone): string
    {
        $sandbox = app()->environment('testing')
            || (bool) config('fuelcab.notifications.otp.sandbox', false);

        $code = $sandbox
            ? (string) config('fuelcab.notifications.otp.sandbox_code', '123456')
            : (string) random_int(100000, 999999);

        $ttlSeconds = (int) config('fuelcab.notifications.otp.expiry_minutes', 10) * 60;

        Cache::put('otp_'.$phone, $code, $ttlSeconds);

        Log::info('[SendOtpAction] OTP Generated.', [
            'phone' => $phone,
            'sandbox' => $sandbox,
        ]);

        if (! $sandbox) {
            event(new OtpRequested($phone, $code));
        }

        return $code;
    }
}
