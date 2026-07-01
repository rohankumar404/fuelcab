<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Events;

use App\Modules\Fuel\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}
}
