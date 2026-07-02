<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Order\Models\OrderStatusLog;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderAccepted;
use App\Modules\Order\Events\OrderAssigned;
use App\Modules\Order\Events\OrderDispatched;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Events\OrderCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class LogOrderStatusChange implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(mixed $event): void
    {
        $order = $event->order;
        $toStatus = $order->status;

        // Determine fromStatus deterministically from transition event types
        $fromStatus = match (true) {
            $event instanceof OrderCreated => null,
            $event instanceof OrderAccepted => OrderStatus::Pending,
            $event instanceof OrderAssigned => OrderStatus::Accepted,
            $event instanceof OrderDispatched => OrderStatus::Assigned,
            $event instanceof OrderCompleted => OrderStatus::OutForDelivery,
            $event instanceof OrderCancelled => $event->fromStatus,
            default => null,
        };

        $reason = property_exists($event, 'reason') ? $event->reason : null;
        $changedBy = null;

        if (Auth::check()) {
            $changedBy = Auth::id();
        } elseif (property_exists($event, 'cancelledBy')) {
            $changedBy = $event->cancelledBy;
        }

        OrderStatusLog::create([
            'order_id'    => $order->id,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'reason'      => $reason,
            'changed_by'  => $changedBy,
        ]);
    }
}
