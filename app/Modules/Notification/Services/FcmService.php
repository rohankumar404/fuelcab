<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Models\PushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $serverKey;
    protected string $senderId;
    protected string $endpoint = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = (string) config('fuelcab.notifications.fcm.server_key', '');
        $this->senderId  = (string) config('fuelcab.notifications.fcm.sender_id', '');
    }

    /**
     * Send a push notification to one or more FCM tokens using the legacy HTTP API.
     * Handles automatic deactivation of invalid/expired tokens.
     */
    public function sendNotification(array $tokens, string $title, string $body, array $data = []): bool
    {
        $tokens = array_values(array_unique(array_filter($tokens)));

        if (empty($tokens)) {
            Log::debug('[FcmService] No tokens provided to sendNotification.');
            return false;
        }

        // Enable global skip or logs in local/testing when no key is configured
        if (empty($this->serverKey)) {
            Log::info('[FcmService] Skip real FCM request — FCM server key is empty. Logging payload:', [
                'tokens' => $tokens,
                'title'  => $title,
                'body'   => $body,
                'data'   => $data,
            ]);
            return true;
        }

        try {
            $payload = [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // standard flutter trigger if needed
                ]),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post($this->endpoint, $payload);

            if (! $response->successful()) {
                Log::error('[FcmService] FCM HTTP request failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }

            $responseBody = $response->json();
            Log::info('[FcmService] Push notification request dispatched', [
                'tokens_count' => count($tokens),
                'success_count'=> $responseBody['success'] ?? 0,
                'failure_count'=> $responseBody['failure'] ?? 0,
            ]);

            // Process token deactivations if failure reports exist
            $results = $responseBody['results'] ?? [];
            foreach ($results as $index => $result) {
                if (isset($result['error']) && in_array($result['error'], ['NotRegistered', 'InvalidRegistration'], true)) {
                    $badToken = $tokens[$index] ?? null;
                    if ($badToken) {
                        PushToken::where('token', $badToken)->update(['is_active' => false]);
                        Log::info('[FcmService] Deactivated invalid FCM token', [
                            'token' => $badToken,
                            'error' => $result['error'],
                        ]);
                    }
                }
            }

            return ($responseBody['success'] ?? 0) > 0;

        } catch (\Throwable $e) {
            Log::error('[FcmService] Error executing FCM dispatch', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
