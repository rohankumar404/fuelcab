<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Widgets;

use App\Modules\Order\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersLineChart extends ChartWidget
{
    protected static ?string $heading = 'Orders — Last 30 Days';
    protected static ?int $sort = 2;
    protected static string $color = 'primary';

    protected function getData(): array
    {
        $data   = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date     = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            $data[]   = Order::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => $data,
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
