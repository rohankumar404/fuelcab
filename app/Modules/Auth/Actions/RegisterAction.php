<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Auth\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterAction
{
    /**
     * Register a new customer user.
     *
     * @param  array{
     *   name: string,
     *   email: string,
     *   phone: string,
     *   password: string,
     * } $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role_type' => UserRole::Customer,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole(UserRole::Customer->value);

        event(new UserRegistered($user));

        Log::info('[RegisterAction] User registered.', ['user_id' => $user->id]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
