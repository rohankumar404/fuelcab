<?php

declare(strict_types=1);

namespace App\Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Models\PushToken;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    use ApiResponse;

    /**
     * Register or update a device push token for FCM notifications.
     *
     * Route: POST /api/v1/notifications/push-token
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', 'in:ios,android,web'],
        ]);

        $user = $request->user();

        $pushToken = PushToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'platform' => $validated['platform'],
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return $this->success(
            data: [
                'token' => $pushToken->token,
                'platform' => $pushToken->platform,
                'is_active' => $pushToken->is_active,
                'registered' => true,
            ],
            message: 'Device push token registered successfully.'
        );
    }
}
