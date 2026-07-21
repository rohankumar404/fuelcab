<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Enums\ListingStatus;
use App\Models\Settlement;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Vendor\Models\VendorListing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $vendorId = auth()->user()?->vendor_id;

        if (! $vendorId) {
            return [];
        }

        // Orders scoped to this vendor
        $totalOrders   = Order::where('vendor_id', $vendorId)->count();
        $pendingOrders = Order::where('vendor_id', $vendorId)
            ->where('status', OrderStatus::Pending->value)
            ->count();

        // Listings
        $activeListings  = VendorListing::where('vendor_id', $vendorId)
            ->where('approval_status', ListingStatus::Approved->value)
            ->where('is_active', true)
            ->count();
        $pendingListings = VendorListing::where('vendor_id', $vendorId)
            ->where('approval_status', ListingStatus::PendingApproval->value)
            ->count();

        // Low stock: listings where available_quantity < 100
        $lowStockListings = VendorListing::where('vendor_id', $vendorId)
            ->where('available_quantity', '<', 100)
            ->whereIn('approval_status', [ListingStatus::Approved->value, ListingStatus::Draft->value])
            ->count();

        // Revenue: sum of delivered orders
        $revenue = Order::where('vendor_id', $vendorId)
            ->where('status', OrderStatus::Delivered->value)
            ->sum('total_amount');

        // Pending settlement
        $pendingSettlement = Settlement::where('vendor_id', $vendorId)
            ->where('status', 'pending')
            ->sum('net_payable');

        return [
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('All time orders')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting acceptance')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),

            Stat::make('Active Listings', $activeListings)
                ->description('Approved & live on marketplace')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),

            Stat::make('Pending Listings', $pendingListings)
                ->description('Awaiting admin approval')
                ->descriptionIcon('heroicon-m-hourglass')
                ->color($pendingListings > 0 ? 'info' : 'gray'),

            Stat::make('Revenue', '₹' . number_format((float) $revenue, 0))
                ->description('From delivered orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Settlement', '₹' . number_format((float) $pendingSettlement, 0))
                ->description('Awaiting payout')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($pendingSettlement > 0 ? 'warning' : 'gray'),

            Stat::make('Low Stock Listings', $lowStockListings)
                ->description('Listings with stock < 100 units')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockListings > 0 ? 'danger' : 'gray'),
        ];
    }
}
