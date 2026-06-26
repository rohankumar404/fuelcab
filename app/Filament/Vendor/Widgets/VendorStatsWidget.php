<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Modules\Order\Models\Order;
use App\Modules\Driver\Models\Driver;
use App\Modules\Fuel\Models\Inventory;
use App\Enums\DriverStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user   = auth()->user();
        $vendorId = $user?->vendor_id;

        $todayOrders    = Order::where('vendor_id', $vendorId)->whereDate('created_at', today())->count();
        $todayRevenue   = Order::where('vendor_id', $vendorId)->where('status', 'completed')->whereDate('updated_at', today())->sum('total_amount');
        $activeDrivers  = Driver::where('vendor_id', $vendorId)->where('status', DriverStatus::Available->value)->count();
        $pendingOrders  = Order::where('vendor_id', $vendorId)->where('status', 'pending')->count();
        $lowStockCount  = Inventory::where('vendor_id', $vendorId)->whereColumn('current_stock', '<=', 'reorder_level')->count();

        return [
            Stat::make('Today\'s Orders', $todayOrders)
                ->description('Placed today')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Today\'s Revenue', '₹' . number_format($todayRevenue, 0))
                ->description('From completed orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Drivers', $activeDrivers)
                ->description('Available now')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Low Stock Alerts', $lowStockCount)
                ->description('Products below reorder level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'gray'),
        ];
    }
}
