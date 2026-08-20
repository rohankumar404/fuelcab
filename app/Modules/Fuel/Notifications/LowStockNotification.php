<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Notifications;

use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    public function __construct(private readonly mixed $inventory) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->inventory->product_id,
            'vendor_id' => $this->inventory->vendor_id,
            'quantity_available' => $this->inventory->quantity_available,
            'low_stock_threshold' => $this->inventory->low_stock_threshold,
            'message' => 'Low stock alert: your inventory is running low.',
        ];
    }
}
