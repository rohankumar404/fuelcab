<?php

declare(strict_types=1);

namespace App\Modules\Notification\Channels;

use App\Modules\Notification\Models\Notification as DbNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

/**
 * Custom database channel that writes to the standard Laravel polymorphic
 * notifications table schema (notifiable_type, notifiable_id, data).
 *
 * Compatible with Filament DatabaseNotifications widget and Laravel Notifiable trait.
 */
class CustomDatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @return Model|null
     */
    public function send(mixed $notifiable, Notification $notification): ?DbNotification
    {
        if (! method_exists($notification, 'toArray')) {
            return null;
        }

        $notifiableId = method_exists($notifiable, 'getKey')
            ? $notifiable->getKey()
            : ($notifiable->id ?? null);

        if (! $notifiableId) {
            return null;
        }

        $data = $notification->toArray($notifiable);

        return DbNotification::create([
            'type' => get_class($notification),
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => (string) $notifiableId,
            'data' => $data,
        ]);
    }
}
