<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Models\Settlement;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.vendor.pages.reports';

    // ── Order report filters ─────────────────────────────────────────────────
    public ?string $orderFrom   = null;
    public ?string $orderTo     = null;
    public ?string $orderStatus = null;

    // ── Settlement report filters ────────────────────────────────────────────
    public ?string $settlementFrom   = null;
    public ?string $settlementTo     = null;
    public ?string $settlementStatus = null;

    // ── Summary stats ────────────────────────────────────────────────────────
    public array $orderSummary      = [];
    public array $settlementSummary = [];

    public function mount(): void
    {
        $this->orderFrom       = now()->startOfMonth()->toDateString();
        $this->orderTo         = now()->toDateString();
        $this->settlementFrom  = now()->subMonths(3)->startOfMonth()->toDateString();
        $this->settlementTo    = now()->toDateString();

        $this->refreshSummaries();
    }

    /**
     * Refresh both summary blocks whenever filters change.
     */
    public function refreshSummaries(): void
    {
        $vendorId = auth()->user()?->vendor_id;

        if (! $vendorId) {
            return;
        }

        // Order summary
        $orderQuery = Order::where('vendor_id', $vendorId)
            ->when($this->orderFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->orderFrom))
            ->when($this->orderTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->orderTo))
            ->when($this->orderStatus, fn ($q) => $q->where('status', $this->orderStatus));

        $this->orderSummary = [
            'total'   => $orderQuery->count(),
            'revenue' => $orderQuery->sum('total_amount'),
        ];

        // Settlement summary
        $settlementQuery = Settlement::where('vendor_id', $vendorId)
            ->when($this->settlementFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->settlementFrom))
            ->when($this->settlementTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->settlementTo))
            ->when($this->settlementStatus, fn ($q) => $q->where('status', $this->settlementStatus));

        $this->settlementSummary = [
            'count'      => $settlementQuery->count(),
            'gross'      => $settlementQuery->sum('gross_amount'),
            'commission' => $settlementQuery->sum('commission_amount'),
            'net'        => $settlementQuery->sum('net_payable'),
        ];
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    /**
     * Export orders as CSV, scoped to the authenticated vendor and current filters.
     * SECURITY: vendor_id is always resolved server-side from the authenticated user.
     */
    public function exportOrders(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $vendorId = auth()->user()?->vendor_id;

        if (! $vendorId) {
            Notification::make()->title('No vendor profile found.')->danger()->send();
        }

        $orders = Order::with(['customer', 'deliveryAddress'])
            ->where('vendor_id', $vendorId)
            ->when($this->orderFrom,   fn ($q) => $q->whereDate('created_at', '>=', $this->orderFrom))
            ->when($this->orderTo,     fn ($q) => $q->whereDate('created_at', '<=', $this->orderTo))
            ->when($this->orderStatus, fn ($q) => $q->where('status', $this->orderStatus))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'orders_' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Order Number',
                'Customer Name',
                'Customer Phone',
                'Status',
                'Total Amount (₹)',
                'Channel',
                'City',
                'Created At',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer?->name ?? '',
                    $order->customer?->phone ?? '',
                    is_string($order->status) ? $order->status : $order->status?->value,
                    number_format((float) $order->total_amount, 2),
                    $order->channel ?? '',
                    $order->deliveryAddress?->city ?? '',
                    $order->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export settlements as CSV, scoped to the authenticated vendor.
     */
    public function exportSettlements(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $vendorId = auth()->user()?->vendor_id;

        if (! $vendorId) {
            Notification::make()->title('No vendor profile found.')->danger()->send();
        }

        $settlements = Settlement::where('vendor_id', $vendorId)
            ->when($this->settlementFrom,   fn ($q) => $q->whereDate('created_at', '>=', $this->settlementFrom))
            ->when($this->settlementTo,     fn ($q) => $q->whereDate('created_at', '<=', $this->settlementTo))
            ->when($this->settlementStatus, fn ($q) => $q->where('status', $this->settlementStatus))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'settlements_' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($settlements) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Period',
                'Gross Amount (₹)',
                'Commission (₹)',
                'Adjustments (₹)',
                'Net Payable (₹)',
                'Status',
                'Payout Reference',
                'Created At',
            ]);

            foreach ($settlements as $s) {
                fputcsv($handle, [
                    $s->created_at?->format('M Y') ?? '',
                    number_format((float) $s->gross_amount, 2),
                    number_format((float) $s->commission_amount, 2),
                    number_format((float) ($s->adjustments ?? 0), 2),
                    number_format((float) $s->net_payable, 2),
                    $s->status,
                    $s->payout_reference ?? '',
                    $s->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
