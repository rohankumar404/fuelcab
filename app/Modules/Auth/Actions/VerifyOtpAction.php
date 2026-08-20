<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VerifyOtpAction
{
    /**
     * Verify an OTP and retrieve or create the user.
     *
     * @return array{user: User, token: string, is_new: bool}
     */
    public function execute(string $phone, string $otp): array
    {
        $cachedCode = Cache::get('otp_'.$phone);

        if (! $cachedCode || $cachedCode !== $otp) {
            throw new InvalidArgumentException('Invalid or expired OTP code.');
        }

        // Clear OTP on success
        Cache::forget('otp_'.$phone);
        Cache::forget('otp_resend_'.$phone);

        $user = User::where('phone', $phone)->first();
        $isNewUser = false;

        if (! $user) {
            $isNewUser = true;
            $user = User::create([
                'name' => 'Customer '.substr($phone, -4),
                'email' => 'user_'.Str::random(10).'@fuelcab.com',
                'phone' => $phone,
                'password' => Hash::make(Str::random(24)),
                'role_type' => UserRole::Customer,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $user->assignRole(UserRole::Customer->value);
        }

        Log::info('[VerifyOtpAction] OTP Verified.', [
            'phone' => $phone,
            'is_new' => $isNewUser,
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('otp-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'is_new' => $isNewUser,
        ];
    }
}
