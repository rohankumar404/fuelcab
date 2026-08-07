<?php

declare(strict_types=1);

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Jobs\SendPushNotificationJob;
use App\Modules\Notification\Models\PushToken;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        // Check if push notifications are enabled globally
        if (! config('fuelcab.notifications.channels.push', true)) {
            Log::debug('[FcmChannel] Push notifications are globally disabled in config.');
            return;
        }

        // Resolve active tokens for this notifiable
        $tokens = $this->getTokens($notifiable);
        if (empty($tokens)) {
            Log::debug('[FcmChannel] No active push tokens found for notifiable.', [
                'notifiable_id'   => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : ($notifiable->id ?? null),
                'notifiable_type' => get_class($notifiable),
            ]);
            return;
        }

        // Resolve message payload
        $payload = [];
        if (method_exists($notification, 'toFcm')) {
            $payload = $notification->toFcm($notifiable);
        } elseif (method_exists($notification, 'toArray')) {
            $data = $notification->toArray($notifiable);
            $payload = [
                'title' => $data['title'] ?? 'New Notification',
                'body'  => $data['message'] ?? $data['body'] ?? 'You have a new update.',
                'data'  => $data,
            ];
        }

        if (empty($payload)) {
            Log::debug('[FcmChannel] Notification did not resolve FCM payload.', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        $title = $payload['title'] ?? 'Notification';
        $body  = $payload['body'] ?? '';
        $data  = $payload['data'] ?? [];

        // Dispatch SendPushNotificationJob to the queue to prevent blocking the current process
        SendPushNotificationJob::dispatch($tokens, $title, $body, $data);
    }

    /**
     * Extract push tokens from the notifiable entity.
     */
    protected function getTokens(mixed $notifiable): array
    {
        $userId = null;

        // If the notifiable is a User model directly
        if ($notifiable instanceof \App\Models\User) {
            $userId = $notifiable->id;
        } elseif (isset($notifiable->user_id)) {
            $userId = $notifiable->user_id;
        } elseif (method_exists($notifiable, 'user')) {
            $userId = optional($notifiable->user())->id;
        }

        if (! $userId) {
            return [];
        }

        return PushToken::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }
}
