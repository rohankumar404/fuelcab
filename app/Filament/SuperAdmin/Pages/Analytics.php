<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Modules\Fuel\Models\Product;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Vendor\Models\Vendor;
use Filament\Pages\Page;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'SYSTEM';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.super-admin.pages.analytics';

    public array $stats = [];

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        // 1. Order Status breakdown
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 2. Sales Channel breakdown
        $channelStats = Order::selectRaw('channel, SUM(total_amount) as revenue, COUNT(*) as count')
            ->groupBy('channel')
            ->get()
            ->mapWithKeys(fn ($item) => [
                (is_string($item->channel) ? $item->channel : $item->channel->value) => [
                    'revenue' => (float) $item->revenue,
                    'count' => (int) $item->count,
                ],
            ])->toArray();

        // 3. Monthly Sales (last 6 months) - driver aware
        $driver = config('database.default');
        $monthExpr = match ($driver) {
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => "strftime('%Y-%m', created_at)", // sqlite
        };

        $monthlyRevenue = Order::whereIn('status', [OrderStatus::Delivered->value, 'completed', 'delivered'])
            ->selectRaw("{$monthExpr} as month, SUM(total_amount) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->limit(6)
            ->pluck('revenue', 'month')
            ->toArray();

        // 4. Top Performing Vendors
        $topVendors = Order::whereNotNull('vendor_id')
            ->whereIn('status', [OrderStatus::Delivered->value, 'completed', 'delivered'])
            ->selectRaw('vendor_id, SUM(total_amount) as total_sales, COUNT(*) as total_orders')
            ->groupBy('vendor_id')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $vendor = Vendor::find($item->vendor_id);

                return [
                    'brand_name' => $vendor?->brand_name ?? 'Unknown Vendor',
                    'total_sales' => (float) $item->total_sales,
                    'total_orders' => (int) $item->total_orders,
                ];
            })->toArray();

        // 5. Top Selling Products
        $topProducts = OrderItem::selectRaw('product_id, SUM(quantity) as quantity_sold, SUM(total_price) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);

                return [
                    'name' => $product?->name ?? 'Unknown Product',
                    'quantity_sold' => (float) $item->quantity_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            })->toArray();

        $this->stats = [
            'orders_by_status' => $ordersByStatus,
            'channel_stats' => $channelStats,
            'monthly_revenue' => $monthlyRevenue,
            'top_vendors' => $topVendors,
            'top_products' => $topProducts,
        ];
    }
}
