<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Listeners;

use App\Modules\Fuel\Events\InventorySynced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyLowStock implements ShouldQueue
{
    public function handle(InventorySynced $event): void
    {
        $inventory = $event->inventory;

        if ($inventory->quantity_available <= $inventory->low_stock_threshold) {
            Log::warning('FuelCab: Low stock alert', [
                'product_id'         => $inventory->product_id,
                'vendor_id'          => $inventory->vendor_id,
                'quantity_available' => $inventory->quantity_available,
                'low_stock_threshold'=> $inventory->low_stock_threshold,
            ]);

            // TODO: Dispatch notification to vendor via NotificationService
        }
    }
}
