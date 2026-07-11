<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitOfMeasure: string
{
    case Litres       = 'litres';
    case Kilograms    = 'kilograms';
    case MetricTonnes = 'metric_tonnes';
    case Units        = 'units';

    public function label(): string
    {
        return match($this) {
            self::Litres       => 'Litres',
            self::Kilograms    => 'Kilograms',
            self::MetricTonnes => 'Metric Tonnes',
            self::Units        => 'Units',
        };
    }
}
