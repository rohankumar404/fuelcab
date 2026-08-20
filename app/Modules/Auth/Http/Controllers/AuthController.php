<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Events\OtpRequested;
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\PasswordResetMail;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    // ─────────────────────────────────────────────────────────────────────────
    //  Constants
    // ─────────────────────────────────────────────────────────────────────────

    private const OTP_CACHE_PREFIX = 'otp_';

    private const RESEND_CACHE_PREFIX = 'otp_resend_';

    // ─────────────────────────────────────────────────────────────────────────
    //  Registration / Login
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role_type' => UserRole::Customer,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole(UserRole::Customer->value);

        // Fire welcome email event
        event(new UserRegistered($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required_without:phone|email|nullable',
            'phone' => 'required_without:email|string|nullable',
            'password' => 'required|string',
        ]);

        $query = User::query();

        if (! empty($validated['email'])) {
            $query->where('email', $validated['email']);
        } else {
            $query->where('phone', $validated['phone']);
        }

        $user = $query->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error('Invalid credentials', 'The provided credentials do not match our records.', 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Successfully authenticated');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  OTP Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate and cache an OTP for the given phone number.
     * In sandbox mode the static sandbox_code is used and Authkey is NOT called.
     * In production mode a random code is generated and OtpRequested is fired
     * which queues the Authkey delivery job.
     */
    private function generateAndSendOtp(string $phone): string
    {
        $sandbox = app()->environment('testing')
            || (bool) config('fuelcab.notifications.otp.sandbox', false);

        $code = $sandbox
            ? (string) config('fuelcab.notifications.otp.sandbox_code', '123456')
            : (string) random_int(100000, 999999);

        $ttlSeconds = (int) config('fuelcab.notifications.otp.expiry_minutes', 10) * 60;

        Cache::put(self::OTP_CACHE_PREFIX.$phone, $code, $ttlSeconds);

        Log::info('[OTP] Generated', [
            'phone' => $phone,
            'sandbox' => $sandbox,
        ]);

        // In production: fire event → queued listener → queued job → Authkey HTTP call
        if (! $sandbox) {
            event(new OtpRequested($phone, $code));
        }

        return $code;
    }

    /**
     * Check and increment the resend attempt counter for the given phone.
     * Returns true if the request is allowed, false if rate limited.
     */
    private function checkResendRateLimit(string $phone): bool
    {
        $key = self::RESEND_CACHE_PREFIX.$phone;
        $max = (int) config('fuelcab.notifications.otp.max_resend', 3);
        $window = (int) config('fuelcab.notifications.otp.resend_window', 10) * 60;

        $attempts = (int) Cache::get($key, 0);

        if ($attempts >= $max) {
            return false;
        }

        Cache::put($key, $attempts + 1, $window);

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  OTP Endpoints
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/auth/send-otp
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $validated['phone'];
        $code = $this->generateAndSendOtp($phone);

        $sandbox = app()->environment('testing')
            || (bool) config('fuelcab.notifications.otp.sandbox', false);

        return $this->success([
            'phone' => $phone,
            // Expose OTP only in sandbox / test environments — never in production
            'otp' => $sandbox ? $code : null,
        ], 'OTP sent successfully');
    }

    /**
     * POST /api/v1/auth/resend-otp
     *
     * Rate-limited: max {OTP_MAX_RESEND} requests per {OTP_RESEND_WINDOW_MINUTES} minutes.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $validated['phone'];

        if (! $this->checkResendRateLimit($phone)) {
            $window = config('fuelcab.notifications.otp.resend_window', 10);

            return $this->error(
                'Too many requests',
                "You have exceeded the OTP resend limit. Please try again in {$window} minutes.",
                429
            );
        }

        $code = $this->generateAndSendOtp($phone);
        $sandbox = app()->environment('testing')
            || (bool) config('fuelcab.notifications.otp.sandbox', false);

        return $this->success([
            'phone' => $phone,
            'otp' => $sandbox ? $code : null,
        ], 'OTP resent successfully');
    }

    /**
     * POST /api/v1/auth/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        $phone = $validated['phone'];
        $otp = $validated['otp'];

        $cachedCode = Cache::get(self::OTP_CACHE_PREFIX.$phone);

        if (! $cachedCode || $cachedCode !== $otp) {
            return $this->error('Verification failed', 'Invalid or expired OTP code.', 422);
        }

        // Clear OTP once successfully verified (single-use)
        Cache::forget(self::OTP_CACHE_PREFIX.$phone);

        // Clear resend counter on successful verification
        Cache::forget(self::RESEND_CACHE_PREFIX.$phone);

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

        Log::info('[OTP] Verified', [
            'phone' => $phone,
            'is_new' => $isNewUser,
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('otp-auth-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'is_new_user' => $isNewUser,
        ], 'OTP verified successfully');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Password Reset (Email OTP Flow)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/auth/forgot-password
     * Sends a 6-digit OTP to the user's email address.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $otp = (string) random_int(100000, 999999);
        $expiry = (int) config('fuelcab.notifications.otp.expiry_minutes', 10);
        $cacheKey = 'pwd_reset_'.md5($validated['email']);

        Cache::put($cacheKey, $otp, now()->addMinutes($expiry));

        SendEmailJob::dispatch($user->email, new PasswordResetMail($user->name, $otp, $expiry));

        return $this->success(null, 'A password reset code has been sent to your email address.');
    }

    /**
     * POST /api/v1/auth/verify-reset-otp
     * Validates the reset OTP and returns a temporary signed reset token.
     */
    public function verifyResetOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|digits:6',
        ]);

        $cacheKey = 'pwd_reset_'.md5($validated['email']);
        $cached = Cache::get($cacheKey);

        if (! $cached || $cached !== $validated['otp']) {
            return $this->error('Invalid or expired OTP.', null, 422);
        }

        // Exchange OTP for a short-lived reset token
        $resetToken = Str::random(64);
        Cache::put('pwd_reset_token_'.$resetToken, $validated['email'], now()->addMinutes(15));
        Cache::forget($cacheKey);

        return $this->success(['reset_token' => $resetToken], 'OTP verified. Use the reset token to set a new password.');
    }

    /**
     * POST /api/v1/auth/reset-password
     * Resets the password using the signed reset token from verifyResetOtp.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $tokenKey = 'pwd_reset_token_'.$validated['reset_token'];
        $email = Cache::get($tokenKey);

        if (! $email) {
            return $this->error('Reset token is invalid or has expired.', null, 422);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return $this->error('User not found.', null, 404);
        }

        $user->update(['password' => Hash::make($validated['password'])]);
        Cache::forget($tokenKey);

        // Revoke all previous tokens for security
        $user->tokens()->delete();

        Log::info('[AuthController] Password reset successful', ['user_id' => $user->id]);

        return $this->success(null, 'Password has been reset successfully. Please log in with your new credentials.');
    }
}
