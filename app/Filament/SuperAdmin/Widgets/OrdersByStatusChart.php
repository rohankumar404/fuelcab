<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Widgets;

use App\Modules\Order\Models\Order;
use App\Enums\OrderStatus;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Orders by Status';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $labels = [];
        $data   = [];
        $colors = [
            'pending'   => '#94a3b8',
            'confirmed' => '#f59e0b',
            'en_route'  => '#38bdf8',
            'arrived'   => '#818cf8',
            'completed' => '#10b981',
            'cancelled' => '#f43f5e',
        ];

        foreach (OrderStatus::cases() as $status) {
            $labels[] = ucfirst(str_replace('_', ' ', $status->value));
            $data[]   = Order::where('status', $status->value)->count();
        }

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => array_values($colors),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
