<?php

declare(strict_types=1);

namespace App\Modules\Order\Events;

use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $fromStatus,
        public readonly ?string $reason = null,
        public readonly ?string $cancelledBy = null
    ) {}
}
