<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Settlement;
use App\Models\User;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Enums\SalesChannel;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'SYSTEM';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.super-admin.pages.reports';

    public ?string $from = null;
    public ?string $to = null;

    public array $summary = [];

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();

        $this->refreshSummary();
    }

    public function refreshSummary(): void
    {
        $orderQuery = Order::query()
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        $paymentQuery = Payment::query()
            ->where('status', 'success')
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        $settlementQuery = Settlement::query()
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        $this->summary = [
            'total_orders'       => $orderQuery->count(),
            'total_revenue'      => $paymentQuery->sum('amount'),
            'total_commission'   => $settlementQuery->sum('commission_amount'),
            'settled_amount'     => $settlementQuery->where('status', 'processed')->sum('net_payable'),
        ];
    }

    public function exportDirectOrders(): StreamedResponse
    {
        $orders = Order::query()
            ->with(['customer', 'deliveryAddress'])
            ->whereNull('vendor_id')
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'direct_orders_' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Customer Name',
                'Customer Phone',
                'Status',
                'Subtotal (₹)',
                'Tax Amount (₹)',
                'Delivery Fee (₹)',
                'Total Amount (₹)',
                'Channel',
                'City',
                'State',
                'Created At',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer?->name ?? '',
                    $order->customer?->phone ?? '',
                    is_string($order->status) ? $order->status : $order->status?->value,
                    number_format((float) $order->subtotal_amount, 2, '.', ''),
                    number_format((float) $order->tax_amount, 2, '.', ''),
                    number_format((float) $order->delivery_fee, 2, '.', ''),
                    number_format((float) $order->total_amount, 2, '.', ''),
                    is_string($order->channel) ? $order->channel : $order->channel?->value,
                    $order->deliveryAddress?->city ?? '',
                    $order->deliveryAddress?->state ?? '',
                    $order->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportMarketplaceOrders(): StreamedResponse
    {
        $orders = Order::query()
            ->with(['customer', 'vendor', 'deliveryAddress'])
            ->whereNotNull('vendor_id')
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'marketplace_orders_' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Vendor Name',
                'Customer Name',
                'Status',
                'Subtotal (₹)',
                'Tax Amount (₹)',
                'Delivery Fee (₹)',
                'Total Amount (₹)',
                'City',
                'Created At',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->vendor?->brand_name ?? '',
                    $order->customer?->name ?? '',
                    is_string($order->status) ? $order->status : $order->status?->value,
                    number_format((float) $order->subtotal_amount, 2, '.', ''),
                    number_format((float) $order->tax_amount, 2, '.', ''),
                    number_format((float) $order->delivery_fee, 2, '.', ''),
                    number_format((float) $order->total_amount, 2, '.', ''),
                    $order->deliveryAddress?->city ?? '',
                    $order->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportSettlements(): StreamedResponse
    {
        $settlements = Settlement::query()
            ->with('vendor')
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'settlements_' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($settlements) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Vendor Brand Name',
                'Gross Amount (₹)',
                'Commission Amount (₹)',
                'Adjustments (₹)',
                'Net Payable (₹)',
                'Status',
                'Payout Reference',
                'Created At',
            ]);

            foreach ($settlements as $s) {
                fputcsv($handle, [
                    $s->vendor?->brand_name ?? '',
                    number_format((float) $s->gross_amount, 2, '.', ''),
                    number_format((float) $s->commission_amount, 2, '.', ''),
                    number_format((float) ($s->adjustments ?? 0), 2, '.', ''),
                    number_format((float) $s->net_payable, 2, '.', ''),
                    $s->status,
                    $s->payout_reference ?? '',
                    $s->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportUsers(): StreamedResponse
    {
        $users = User::query()
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'users_list_' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Name',
                'Email',
                'Mobile',
                'Role Type',
                'Status / Email Verified At',
                'Created At',
            ]);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->mobile,
                    is_string($user->role_type) ? $user->role_type : $user->role_type?->value,
                    $user->email_verified_at ? 'Verified' : 'Unverified',
                    $user->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
