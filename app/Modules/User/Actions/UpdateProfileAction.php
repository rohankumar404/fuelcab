<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class UpdateProfileAction
{
    /**
     * Allowed fields a user can self-update.
     */
    private const ALLOWED_FIELDS = ['name', 'phone', 'avatar'];

    /**
     * Update an authenticated user's profile.
     *
     * @param  array<string, mixed>  $data  Only allowed fields will be applied.
     * @param  string|null  $newPassword  If provided, password will be updated.
     */
    public function execute(string $userId, array $data, ?string $newPassword = null): User
    {
        $user = User::findOrFail($userId);

        $updateData = array_intersect_key($data, array_flip(self::ALLOWED_FIELDS));

        // Ensure phone uniqueness if being updated
        if (isset($updateData['phone']) && $updateData['phone'] !== $user->phone) {
            $exists = User::where('phone', $updateData['phone'])
                ->where('id', '!=', $userId)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException('The phone number is already in use by another account.');
            }
        }

        if ($newPassword !== null) {
            $updateData['password'] = Hash::make($newPassword);
        }

        $user->update($updateData);

        Log::info('[UpdateProfileAction] User profile updated.', [
            'user_id' => $userId,
            'fields' => array_keys($updateData),
        ]);

        return $user->fresh();
    }
}
