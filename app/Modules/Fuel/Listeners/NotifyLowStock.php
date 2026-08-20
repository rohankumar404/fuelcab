<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Listeners;

use App\Models\User;
use App\Modules\Fuel\Events\InventorySynced;
use App\Modules\Fuel\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyLowStock implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(InventorySynced $event): void
    {
        $inventory = $event->inventory;

        if ($inventory->quantity_available > $inventory->low_stock_threshold) {
            return;
        }

        Log::warning('[NotifyLowStock] Low stock detected.', [
            'product_id' => $inventory->product_id,
            'vendor_id' => $inventory->vendor_id,
            'quantity_available' => $inventory->quantity_available,
            'low_stock_threshold' => $inventory->low_stock_threshold,
        ]);

        // Notify the vendor admin user(s) via database notification
        $vendorAdmins = User::where('vendor_id', $inventory->vendor_id)
            ->where('role_type', 'vendor_admin')
            ->get();

        foreach ($vendorAdmins as $admin) {
            $admin->notify(new LowStockNotification($inventory));
        }
    }
}
