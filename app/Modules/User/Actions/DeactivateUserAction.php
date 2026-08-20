<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class DeactivateUserAction
{
    /**
     * Deactivate a user account, disabling logins and revoking tokens.
     */
    public function execute(string $userId): User
    {
        $user = User::findOrFail($userId);

        $user->update([
            'status' => 'inactive',
        ]);

        // Revoke all existing Sanctum API tokens
        $user->tokens()->delete();

        Log::info('[DeactivateUserAction] User account deactivated.', [
            'user_id' => $userId,
        ]);

        return $user->fresh();
    }
}
