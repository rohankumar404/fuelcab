<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Assigned = 'assigned';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * Get valid transitions for this status.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Accepted, self::Cancelled],
            self::Accepted => [self::Assigned, self::Cancelled],
            self::Assigned => [self::OutForDelivery, self::Cancelled],
            self::OutForDelivery => [self::Delivered, self::Cancelled],
            self::Delivered, self::Cancelled => [], // terminal states
        };
    }

    /**
     * Check if status can transition to target status.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Assigned => 'Assigned',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
