<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Enums\ListingStatus;
use App\Models\Settlement;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Vendor\Models\VendorListing;
use Filament\Pages\Page;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.vendor.pages.analytics';

    public array $stats = [];

    public function mount(): void
    {
        $vendorId = auth()->user()?->vendor_id;

        if (! $vendorId) {
            return;
        }

        // Orders by status
        $ordersByStatus = Order::where('vendor_id', $vendorId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Monthly revenue (last 6 months)
        $monthlyRevenue = Order::where('vendor_id', $vendorId)
            ->where('status', OrderStatus::Delivered->value)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("strftime('%Y-%m', created_at) as month, SUM(total_amount) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // Listings summary
        $totalListings    = VendorListing::where('vendor_id', $vendorId)->count();
        $approvedListings = VendorListing::where('vendor_id', $vendorId)
            ->where('approval_status', ListingStatus::Approved->value)->count();
        $lowStock         = VendorListing::where('vendor_id', $vendorId)
            ->where('available_quantity', '<', 100)->count();

        // Settlements
        $totalGross     = Settlement::where('vendor_id', $vendorId)->sum('gross_amount');
        $totalNet       = Settlement::where('vendor_id', $vendorId)->sum('net_payable');
        $totalCommission = Settlement::where('vendor_id', $vendorId)->sum('commission_amount');

        $this->stats = [
            'orders_by_status' => $ordersByStatus,
            'monthly_revenue'  => $monthlyRevenue,
            'total_listings'   => $totalListings,
            'approved_listings' => $approvedListings,
            'low_stock'        => $lowStock,
            'total_gross'      => $totalGross,
            'total_net'        => $totalNet,
            'total_commission' => $totalCommission,
        ];
    }
}
