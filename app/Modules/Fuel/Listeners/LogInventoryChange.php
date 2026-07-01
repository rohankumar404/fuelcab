<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Listeners;

use App\Modules\Fuel\Events\InventorySynced;
use App\Modules\Fuel\Models\InventoryLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogInventoryChange implements ShouldQueue
{
    public function handle(InventorySynced $event): void
    {
        InventoryLog::create([
            'inventory_id'     => $event->inventory->id,
            'product_id'       => $event->inventory->product_id,
            'vendor_id'        => $event->inventory->vendor_id,
            'type'             => 'sync',
            'quantity_before'  => $event->quantityBefore,
            'quantity_changed' => $event->quantityAfter - $event->quantityBefore,
            'quantity_after'   => $event->quantityAfter,
            'reference_type'   => $event->referenceType,
            'reference_id'     => $event->referenceId,
        ]);
    }
}
