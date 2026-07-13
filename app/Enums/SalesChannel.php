<?php

declare(strict_types=1);

namespace App\Enums;

enum SalesChannel: string
{
    case Direct      = 'direct';
    case Marketplace = 'marketplace';

    public function label(): string
    {
        return match($this) {
            self::Direct      => 'FuelCab Direct',
            self::Marketplace => 'Marketplace',
        };
    }

    /**
     * Whether FuelCab is the operator/fulfiller for this channel.
     */
    public function isFirstParty(): bool
    {
        return $this === self::Direct;
    }
}
