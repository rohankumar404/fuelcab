<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Widgets;

use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Vendor\Models\Vendor;
use App\Models\User;
use App\Enums\DriverStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayOrders   = Order::whereDate('created_at', today())->count();
        $weekOrders    = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthRevenue  = Payment::where('status', 'success')->whereMonth('created_at', now()->month)->sum('amount');
        $activeVendors = Vendor::where('status', 'approved')->count();
        $onlineDrivers = \App\Modules\Driver\Models\Driver::where('status', DriverStatus::Available->value)->count();
        $totalCustomers = User::where('role_type', 'customer')->count();

        return [
            Stat::make('Today\'s Orders', $todayOrders)
                ->description("This week: {$weekOrders}")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Monthly Revenue', '₹' . number_format($monthRevenue, 0))
                ->description('Successful payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Vendors', $activeVendors)
                ->description('Approved vendors')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('Drivers Online', $onlineDrivers)
                ->description('Currently available')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make('Total Customers', $totalCustomers)
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
