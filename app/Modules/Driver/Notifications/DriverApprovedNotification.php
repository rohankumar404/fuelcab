<?php

declare(strict_types=1);

namespace App\Modules\Driver\Notifications;

use App\Modules\Driver\Models\Driver;
use Illuminate\Notifications\Notification;

class DriverApprovedNotification extends Notification
{
    public function __construct(private readonly Driver $driver) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'driver_approved',
            'driver_id' => $this->driver->id,
            'message' => 'Your driver account has been approved. You can now start accepting deliveries.',
        ];
    }
}
