<?php

declare(strict_types=1);

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Models\Notification as DbNotification;
use Illuminate\Notifications\Notification;

class CustomDatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toArray')) {
            return null;
        }

        $data = $notification->toArray($notifiable);
        $type = $data['type'] ?? class_basename($notification);
        $message = $data['message'] ?? $data['body'] ?? '';
        $title = $data['title'] ?? ucwords(str_replace(['_', '-'], ' ', $type));

        // Ensure we have a user_id
        $userId = method_exists($notifiable, 'getKey') ? $notifiable->getKey() : ($notifiable->id ?? null);

        if (! $userId) {
            return null;
        }

        return DbNotification::create([
            'user_id' => $userId,
            'type'    => substr($type, 0, 50),
            'title'   => $title,
            'body'    => $message,
            'status'  => 'sent',
        ]);
    }
}
