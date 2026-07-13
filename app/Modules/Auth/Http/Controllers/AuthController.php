<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Modules\Auth\Http\Resources\AuthTokenResource;
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

    /**
     * POST /api/v1/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'],
            'password'          => Hash::make($validated['password']),
            'role_type'         => UserRole::Customer,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole(UserRole::Customer->value);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 'User registered successfully', 201);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required_without:phone|email|nullable',
            'phone'    => 'required_without:email|string|nullable',
            'password' => 'required|string',
        ]);

        $query = User::query();

        if (!empty($validated['email'])) {
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
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 'Successfully authenticated');
    }

    /**
     * POST /api/v1/auth/send-otp
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $validated['phone'];

        // Under testing or local, use a static code, otherwise random
        $code = app()->environment('testing', 'local') ? '123456' : (string) rand(100000, 999999);

        // Store code in cache for 5 minutes
        Cache::put("otp_{$phone}", $code, 300);

        Log::info("OTP for {$phone} is: {$code}");

        // In a real application, call Authkey.io request URL:
        // Http::get("https://api.authkey.io/request?authkey=...&mobile={$phone}&sid=...&otp={$code}");

        return $this->success([
            'phone' => $phone,
            'otp'   => app()->environment('testing', 'local') ? $code : null, // expose only for tests/local
        ], 'OTP sent successfully');
    }

    /**
     * POST /api/v1/auth/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string',
        ]);

        $phone = $validated['phone'];
        $otp   = $validated['otp'];

        $cachedCode = Cache::get("otp_{$phone}");

        if (! $cachedCode || $cachedCode !== $otp) {
            return $this->error('Verification failed', 'Invalid or expired OTP code.', 422);
        }

        // Clear OTP once verified
        Cache::forget("otp_{$phone}");

        $user = User::where('phone', $phone)->first();
        $isNewUser = false;

        if (! $user) {
            // Register as new Customer
            $isNewUser = true;
            $user = User::create([
                'name'              => 'Customer ' . substr($phone, -4),
                'email'             => 'user_' . Str::random(10) . '@fuelcab.com',
                'phone'             => $phone,
                'password'          => Hash::make(Str::random(24)),
                'role_type'         => UserRole::Customer,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);

            $user->assignRole(UserRole::Customer->value);
        }

        $token = $user->createToken('otp-auth-token')->plainTextToken;

        return $this->success([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'is_new_user'  => $isNewUser,
        ], 'OTP verified successfully');
    }
}
