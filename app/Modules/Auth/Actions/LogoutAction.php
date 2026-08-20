<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogoutAction
{
    /**
     * Revoke the current user's authenticated token.
     */
    public function execute(User $user): void
    {
        $user->currentAccessToken()?->delete();

        Log::info('[LogoutAction] User logged out.', ['user_id' => $user->id]);
    }
}
