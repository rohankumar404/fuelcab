<?php

declare(strict_types=1);

namespace App\Filament\Operations\Pages;

use App\Enums\SalesChannel;
use App\Modules\Fuel\Models\FuelInventory;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?string $navigationLabel = 'Operations Reports';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.operations.pages.reports';

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
        $directOrderQuery = Order::query()
            ->where('channel', SalesChannel::Direct->value)
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to));

        $inventoryCount = FuelInventory::count();
        $lowStockCount = FuelInventory::whereColumn('quantity_available', '<=', 'reorder_threshold')->count();

        $this->summary = [
            'direct_orders_count' => $directOrderQuery->count(),
            'direct_revenue' => $directOrderQuery->whereIn('status', [OrderStatus::Delivered->value, 'delivered'])->sum('total_amount'),
            'total_items_depot' => $inventoryCount,
            'low_stock_alerts' => $lowStockCount,
        ];
    }

    public function exportDirectSales(): StreamedResponse
    {
        $orders = Order::query()
            ->with(['customer', 'deliveryAddress'])
            ->where('channel', SalesChannel::Direct->value)
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'direct_sales_report_'.now()->format('Ymd_His').'.csv';

        return Response::streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Customer Name',
                'Customer Contact',
                'Fulfillment Status',
                'Subtotal (₹)',
                'Tax Amount (₹)',
                'Delivery Fee (₹)',
                'Total Paid (₹)',
                'Destination City',
                'Fulfillment Date',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer?->name ?? 'N/A',
                    $order->customer?->phone ?? 'N/A',
                    is_string($order->status) ? $order->status : $order->status?->value,
                    number_format((float) $order->subtotal_amount, 2, '.', ''),
                    number_format((float) $order->tax_amount, 2, '.', ''),
                    number_format((float) $order->delivery_fee, 2, '.', ''),
                    number_format((float) $order->total_amount, 2, '.', ''),
                    $order->deliveryAddress?->city ?? 'N/A',
                    $order->delivered_at?->toDateTimeString() ?? 'Not Yet',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportDriverDeliveries(): StreamedResponse
    {
        $orders = Order::query()
            ->with(['driver.user', 'customer'])
            ->whereNotNull('driver_id')
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'driver_deliveries_report_'.now()->format('Ymd_His').'.csv';

        return Response::streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number',
                'Driver Name',
                'License Number',
                'Customer Name',
                'Fulfillment Status',
                'Delivered At',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->driver?->user?->name ?? 'N/A',
                    $order->driver?->license_number ?? 'N/A',
                    $order->customer?->name ?? 'N/A',
                    is_string($order->status) ? $order->status : $order->status?->value,
                    $order->delivered_at?->toDateTimeString() ?? 'N/A',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportInventoryReport(): StreamedResponse
    {
        $inventory = FuelInventory::with('product')->get();
        $filename = 'depot_inventory_report_'.now()->format('Ymd_His').'.csv';

        return Response::streamDownload(function () use ($inventory) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Product Name',
                'SKU',
                'Unit of Measure',
                'Quantity Available',
                'Quantity Reserved',
                'Reorder Threshold',
                'Status',
            ]);

            foreach ($inventory as $inv) {
                $status = ($inv->quantity_available <= $inv->reorder_threshold) ? 'LOW STOCK' : 'IN STOCK';
                fputcsv($handle, [
                    $inv->product?->name ?? 'N/A',
                    $inv->product?->sku ?? 'N/A',
                    is_string($inv->product?->unit_of_measure) ? $inv->product->unit_of_measure : ($inv->product?->unit_of_measure?->value ?? 'Litres'),
                    $inv->quantity_available,
                    $inv->quantity_reserved,
                    $inv->reorder_threshold,
                    $status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
