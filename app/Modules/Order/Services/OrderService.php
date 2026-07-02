<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Actions\AcceptOrderAction;
use App\Modules\Order\Actions\AssignDriverAction;
use App\Modules\Order\Actions\UpdateOrderStatusAction;
use App\Modules\Order\Actions\UpdateTrackingLocationAction;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderTracking;
use App\Modules\Order\Enums\OrderStatus;

class OrderService
{
    public function __construct(
        private readonly AcceptOrderAction             $acceptOrder,
        private readonly AssignDriverAction             $assignDriver,
        private readonly UpdateOrderStatusAction       $updateStatus,
        private readonly UpdateTrackingLocationAction $updateLocation,
    ) {}

    /**
     * Accept a pending order.
     */
    public function acceptOrder(string $orderId): Order
    {
        return $this->acceptOrder->execute($orderId);
    }

    /**
     * Assign driver to an order.
     */
    public function assignDriver(string $orderId, string $driverId): Order
    {
        return $this->assignDriver->execute($orderId, $driverId);
    }

    /**
     * Update status of an order.
     */
    public function updateStatus(string $orderId, OrderStatus $status, ?string $reason = null, ?string $cancelledBy = null): Order
    {
        return $this->updateStatus->execute($orderId, $status, $reason, $cancelledBy);
    }

    /**
     * Log driver coordinates.
     */
    public function recordLocation(string $orderId, float $latitude, float $longitude): OrderTracking
    {
        return $this->updateLocation->execute($orderId, $latitude, $longitude);
    }
}
