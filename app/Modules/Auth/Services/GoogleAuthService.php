<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\DTOs\GoogleLoginDTO;
use App\Enums\UserRole;
use Illuminate\Support\Str;

class GoogleAuthService
{
    /**
     * Handle authentication/registration of Google User.
     *
     * @param GoogleLoginDTO $dto
     * @return array{user: User, token: string}
     */
    public function handleGoogleUser(GoogleLoginDTO $dto): array
    {
        // 1. Check if user already has Google ID linked
        $user = User::where('google_id', $dto->googleId)->first();

        if ($user) {
            // Update token/avatar if changed
            $user->update([
                'google_token' => $dto->token,
                'google_avatar' => $dto->avatar,
            ]);
        } else {
            // 2. Link Existing Account check: check if user exists with the same email
            $user = User::where('email', $dto->email)->first();

            if ($user) {
                // Link Google account to existing user account
                $user->update([
                    'google_id' => $dto->googleId,
                    'google_token' => $dto->token,
                    'google_avatar' => $dto->avatar,
                ]);
            } else {
                // 3. Register brand new user
                $role = $dto->roleType ?? UserRole::Customer->value;

                $user = User::create([
                    'name' => $dto->name,
                    'email' => $dto->email,
                    'password' => bcrypt(Str::random(24)), // Random secure password for OAuth user
                    'google_id' => $dto->googleId,
                    'google_token' => $dto->token,
                    'google_avatar' => $dto->avatar,
                    'role_type' => $role,
                    'status' => 'active',
                    'email_verified_at' => now(), // Google emails are pre-verified
                ]);

                // Assign default roles using Spatie (if HasRoles trait is used)
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole($role);
                }
            }
        }

        // Generate Sanctum access token
        $tokenName = 'google-oauth-token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
