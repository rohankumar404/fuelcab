<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class RefreshTokenAction
{
    /**
     * Refresh the current API token for a user.
     *
     * @return array{access_token: string, token_type: string}
     */
    public function execute(User $user): array
    {
        // Revoke current token
        $user->currentAccessToken()?->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('[RefreshTokenAction] Token refreshed.', ['user_id' => $user->id]);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
