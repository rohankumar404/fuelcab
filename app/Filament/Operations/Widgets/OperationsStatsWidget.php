<?php

declare(strict_types=1);

namespace App\Filament\Operations\Widgets;

use App\Modules\Order\Models\Order;
use App\Modules\Driver\Models\Driver;
use App\Modules\Vendor\Models\Vendor;
use App\Enums\DriverStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendingOrders     = Order::where('status', 'pending')->count();
        $unassignedOrders  = Order::where('status', 'confirmed')->whereNull('driver_id')->count();
        $onlineDrivers     = Driver::where('status', DriverStatus::Available->value)->count();
        $pendingVendors    = Vendor::where('status', 'pending')->count();
        $pendingDrivers    = Driver::where('is_approved', false)->count();
        $todayCompleted    = Order::where('status', 'completed')->whereDate('updated_at', today())->count();

        return [
            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Unassigned Orders', $unassignedOrders)
                ->description('Need driver assignment')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Drivers Online', $onlineDrivers)
                ->description('Available now')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),

            Stat::make('Vendor Approvals', $pendingVendors)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('Driver Approvals', $pendingDrivers)
                ->description('Pending verification')
                ->descriptionIcon('heroicon-m-identification')
                ->color('gray'),

            Stat::make('Completed Today', $todayCompleted)
                ->description('Delivered orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
        ];
    }
}
