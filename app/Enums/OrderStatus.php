<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Assigned   = 'assigned';
    case EnRoute    = 'en_route';
    case Delivered  = 'delivered';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case Failed     = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Assigned  => 'Driver Assigned',
            self::EnRoute   => 'En Route',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Failed    => 'Failed',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Failed]);
    }

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::Pending   => in_array($next, [self::Confirmed, self::Cancelled]),
            self::Confirmed => in_array($next, [self::Assigned, self::Cancelled]),
            self::Assigned  => in_array($next, [self::EnRoute, self::Cancelled]),
            self::EnRoute   => in_array($next, [self::Delivered, self::Failed]),
            self::Delivered => in_array($next, [self::Completed]),
            default         => false,
        };
    }
}
