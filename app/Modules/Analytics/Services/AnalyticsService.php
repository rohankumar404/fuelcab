<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Models\User;
use App\Modules\Order\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
     * Compute yesterday's aggregates and cache them.
     */
    public function aggregateDailyStats(): array
    {
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        $stats = [
            'date' => $yesterdayStart->toDateString(),
            'orders_placed' => Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count(),
            'orders_completed' => Order::where('status', 'delivered')
                ->whereBetween('delivered_at', [$yesterdayStart, $yesterdayEnd])
                ->count(),
            'gross_revenue' => (float) Order::where('status', 'delivered')
                ->whereBetween('delivered_at', [$yesterdayStart, $yesterdayEnd])
                ->sum('total_amount'),
            'commission_earned' => (float) Order::where('status', 'delivered')
                ->whereBetween('delivered_at', [$yesterdayStart, $yesterdayEnd])
                ->sum('commission_amount'),
            'new_users' => User::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count(),
        ];

        // Store in cache for 7 days
        Cache::put("daily_stats:{$stats['date']}", $stats, now()->addDays(7));

        Log::info('[AnalyticsService] Daily statistics aggregated.', $stats);

        return $stats;
    }
}
