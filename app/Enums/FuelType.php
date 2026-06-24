<?php

declare(strict_types=1);

namespace App\Enums;

enum FuelType: string
{
    case Petrol  = 'petrol';
    case Diesel  = 'diesel';
    case Cng     = 'cng';
    case Lpg     = 'lpg';
    case Ev      = 'ev';

    public function label(): string
    {
        return match($this) {
            self::Petrol => 'Petrol',
            self::Diesel => 'Diesel',
            self::Cng    => 'CNG',
            self::Lpg    => 'LPG',
            self::Ev     => 'Electric (EV)',
        };
    }
}
