<?php

declare(strict_types=1);

namespace App\Modules\Admin\Actions;

use App\Models\Settlement;
use App\Models\User;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;

class DashboardStatsAction
{
    /**
     * Aggregate key platform statistics for the Super Admin dashboard.
     *
     * @return array{
     *   orders: array{total: int, today: int, pending: int, completed: int, cancelled: int},
     *   revenue: array{total: float, today: float, this_month: float},
     *   vendors: array{total: int, approved: int, pending: int, suspended: int},
     *   users: array{total: int, today: int},
     *   settlements: array{pending_count: int, pending_amount: float},
     * }
     */
    public function execute(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $orders = [
            'total' => Order::count(),
            'today' => Order::where('created_at', '>=', $today)->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $revenue = [
            'total' => (float) Order::where('status', 'delivered')->sum('total_amount'),
            'today' => (float) Order::where('status', 'delivered')
                ->where('delivered_at', '>=', $today)
                ->sum('total_amount'),
            'this_month' => (float) Order::where('status', 'delivered')
                ->where('delivered_at', '>=', $thisMonth)
                ->sum('total_amount'),
        ];

        $vendors = [
            'total' => Vendor::count(),
            'approved' => Vendor::where('status', 'approved')->count(),
            'pending' => Vendor::where('status', 'pending')->count(),
            'suspended' => Vendor::where('status', 'suspended')->count(),
        ];

        $users = [
            'total' => User::count(),
            'today' => User::where('created_at', '>=', $today)->count(),
        ];

        $pendingSettlements = Settlement::where('status', 'pending');
        $settlements = [
            'pending_count' => $pendingSettlements->count(),
            'pending_amount' => (float) $pendingSettlements->sum('net_payable'),
        ];

        return compact('orders', 'revenue', 'vendors', 'users', 'settlements');
    }
}
