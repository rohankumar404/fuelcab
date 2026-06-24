<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending   = 'pending';
    case Initiated = 'initiated';
    case Verified  = 'verified';
    case Captured  = 'captured';
    case Failed    = 'failed';
    case Refunded  = 'refunded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending',
            self::Initiated => 'Initiated',
            self::Verified  => 'Verified',
            self::Captured  => 'Captured',
            self::Failed    => 'Failed',
            self::Refunded  => 'Refunded',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Captured;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Captured, self::Failed, self::Refunded, self::Cancelled]);
    }
}
