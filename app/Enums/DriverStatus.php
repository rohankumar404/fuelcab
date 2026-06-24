<?php

declare(strict_types=1);

namespace App\Enums;

enum DriverStatus: string
{
    case Pending    = 'pending';
    case Active     = 'active';
    case Inactive   = 'inactive';
    case Suspended  = 'suspended';
    case Rejected   = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending Approval',
            self::Active    => 'Active',
            self::Inactive  => 'Inactive',
            self::Suspended => 'Suspended',
            self::Rejected  => 'Rejected',
        };
    }

    public function canAcceptOrders(): bool
    {
        return $this === self::Active;
    }
}
