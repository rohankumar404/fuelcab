<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Authkey.io SMS Gateway Service.
 *
 * API docs: https://authkey.io/docs
 * No secret key — only authkey + SID required.
 *
 * URL format:
 *   https://api.authkey.io/request?authkey=KEY&mobile=PHONE&country_code=91&sid=SID&otp=CODE
 */
class AuthkeySmsService
{
    private readonly string $authkey;

    private readonly string $sid;

    private readonly string $countryCode;

    private readonly string $baseUrl;

    public function __construct()
    {
        $this->authkey = (string) config('fuelcab.notifications.authkey.key', '');
        $this->sid = (string) config('fuelcab.notifications.authkey.sid', '');
        $this->countryCode = (string) config('fuelcab.notifications.authkey.country_code', '91');
        $this->baseUrl = (string) config('fuelcab.notifications.authkey.base_url', 'https://api.authkey.io/request');
    }

    /**
     * Send an OTP to the given phone number via Authkey.io.
     *
     * SID 8391 template:
     *   "Onetime password is : {#phoneNumber#} This OTP is valid for {#expireTime#} minutes only..."
     *
     * Note: Despite the confusing name, {#phoneNumber#} holds the OTP code value.
     * Authkey returns HTTP 203 on some errors, so we check for LogID in the body
     * rather than trusting the HTTP status alone.
     *
     * @param  string  $phone  Phone number (with or without country code / + prefix)
     * @param  string  $otp  The OTP code we generated locally
     * @return bool True only if Authkey confirmed submission via LogID
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $mobile = $this->normalizeMobile($phone);
        $expiryMins = (int) config('fuelcab.notifications.otp.expiry_minutes', 10);

        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'authkey' => $this->authkey,
                'mobile' => $mobile,
                'country_code' => $this->countryCode,
                'sid' => $this->sid,
                // SID 8391 template variables:
                'phoneNumber' => $otp,        // {#phoneNumber#} → the OTP digits
                'expireTime' => $expiryMins, // {#expireTime#}  → validity in minutes
            ]);

            $status = $response->status();
            $body = $response->json() ?? ['raw' => $response->body()];

            // Authkey confirms success by returning a LogID field.
            // It sometimes responds HTTP 203 on errors, so don't rely on status alone.
            $hasLogId = isset($body['LogID']) && ! empty($body['LogID']);

            if ($hasLogId) {
                Log::info('[Authkey] OTP dispatched successfully', [
                    'mobile' => $this->maskMobile($mobile),
                    'status' => $status,
                    'logId' => $body['LogID'],
                    'message' => $body['Message'] ?? null,
                ]);

                return true;
            }

            Log::warning('[Authkey] OTP dispatch failed', [
                'mobile' => $this->maskMobile($mobile),
                'status' => $status,
                'response' => $body,
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('[Authkey] OTP HTTP call failed', [
                'mobile' => $this->maskMobile($mobile),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Strip leading + and country code prefix so we pass only the local mobile digits.
     */
    private function normalizeMobile(string $phone): string
    {
        // Remove any spaces or dashes
        $phone = preg_replace('/[\s\-]/', '', $phone) ?? $phone;

        // Remove leading +
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        // Remove country code prefix if present (e.g. "91" from "919876543210")
        if (str_starts_with($phone, $this->countryCode)) {
            $phone = substr($phone, strlen($this->countryCode));
        }

        return $phone;
    }

    /**
     * Mask mobile number for safe log output (e.g. 98XXXXX210).
     */
    private function maskMobile(string $mobile): string
    {
        $len = strlen($mobile);
        if ($len <= 4) {
            return str_repeat('X', $len);
        }

        return substr($mobile, 0, 2).str_repeat('X', $len - 4).substr($mobile, -2);
    }
}
