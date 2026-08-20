<?php

declare(strict_types=1);

namespace App\Modules\Order\Listeners;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\InvoiceMail;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Helpers\InvoicePdfGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateInvoice implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order->load(['customer', 'items.product', 'deliveryAddress']);

        Log::info('OrderModule: Invoice generation queued for order', [
            'order_id' => $order->id,
        ]);

        // Generate the high-fidelity tax invoice PDF file
        try {
            $pdfPath = (new InvoicePdfGenerator)->generate($order);
        } catch (\Throwable $e) {
            Log::error('GenerateInvoice: Failed to generate PDF invoice file', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            $pdfPath = null;
        }

        // Send invoice email to customer if email is available
        if ($order->customer?->email) {
            $item = $order->items->first();
            $productName = $item?->product?->name ?? 'Fuel Product';
            $quantity = (float) ($item?->quantity ?? 0);
            $unitPrice = (float) ($item?->price_per_unit ?? 0);
            $subtotal = (float) $order->subtotal_amount;
            $tax = (float) $order->tax_amount;
            $delivery = (float) $order->delivery_fee;
            $total = (float) $order->total_amount;
            $method = $order->payment_method ?? 'online';

            try {
                SendEmailJob::dispatch(
                    $order->customer->email,
                    new InvoiceMail(
                        customerName: $order->customer->name,
                        orderNumber: $order->id,
                        productName: $productName,
                        quantity: $quantity,
                        unitPrice: $unitPrice,
                        subtotal: $subtotal,
                        tax: $tax,
                        deliveryFee: $delivery,
                        total: $total,
                        paymentMethod: $method,
                        orderId: $order->id,
                        pdfPath: $pdfPath
                    )
                );
            } catch (\Throwable $e) {
                Log::error('GenerateInvoice: Failed to queue invoice email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
