<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class LoginAction
{
    /**
     * Authenticate a user by email or phone and password.
     *
     * @param  array{
     *   email?: string|null,
     *   phone?: string|null,
     *   password: string,
     * } $credentials
     * @return array{user: User, token: string}
     */
    public function execute(array $credentials): array
    {
        $query = User::query();

        if (! empty($credentials['email'])) {
            $query->where('email', $credentials['email']);
        } elseif (! empty($credentials['phone'])) {
            $query->where('phone', $credentials['phone']);
        } else {
            throw new InvalidArgumentException('Email or phone number is required.');
        }

        $user = $query->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        Log::info('[LoginAction] User logged in.', ['user_id' => $user->id]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
