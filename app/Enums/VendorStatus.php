<?php

declare(strict_types=1);

namespace App\Enums;

enum VendorStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Suspended = 'suspended';
    case Rejected  = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending Review',
            self::Approved  => 'Approved',
            self::Suspended => 'Suspended',
            self::Rejected  => 'Rejected',
        };
    }

    public function isOperational(): bool
    {
        return $this === self::Approved;
    }
}
