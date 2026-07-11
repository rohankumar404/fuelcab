<?php

declare(strict_types=1);

namespace App\Enums;

enum FuelType: string
{
    case Diesel      = 'diesel';
    case Cng         = 'cng';
    case Lpg         = 'lpg';
    case Def         = 'def';
    case Lubricants  = 'lubricants';
    case Ev          = 'ev';

    public function label(): string
    {
        return match($this) {
            self::Diesel     => 'Diesel',
            self::Cng        => 'CNG',
            self::Lpg        => 'LPG',
            self::Def        => 'DEF (AdBlue)',
            self::Lubricants => 'Lubricants',
            self::Ev         => 'Electric (EV)',
        };
    }
}
