<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateUserAction
{
    /**
     * Create a new platform user.
     *
     * @param  array{
     *   name: string,
     *   email: string,
     *   phone: string,
     *   password: string,
     *   role_type: string,
     *   vendor_id?: string|null,
     * } $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $role = UserRole::from($data['role_type']);

            $user = User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => trim($data['phone']),
                'password' => Hash::make($data['password']),
                'role_type' => $role,
                'vendor_id' => $data['vendor_id'] ?? null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Assign Spatie role
            $user->assignRole($role->value);

            Log::info('[CreateUserAction] User created.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role_type' => $role->value,
            ]);

            return $user;
        });
    }
}
