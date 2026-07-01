<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Events;

use App\Modules\Fuel\Models\FuelInventory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventorySynced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly FuelInventory $inventory,
        public readonly float $quantityBefore,
        public readonly float $quantityAfter,
        public readonly string $referenceType = 'manual',
        public readonly ?string $referenceId = null,
    ) {}
}
