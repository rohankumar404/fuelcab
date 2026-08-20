<?php

declare(strict_types=1);

namespace App\Modules\Order\Helpers;

use App\Modules\Order\Models\Order;
use Illuminate\Support\Facades\Storage;

class InvoicePdfGenerator
{
    /**
     * Generate a tax invoice mock file for the given order and save it to storage.
     * Calculated on 18% GST (9% CGST + 9% SGST).
     *
     * @return string Absolute file path of the generated invoice
     */
    public function generate(Order $order): string
    {
        $order->loadMissing(['customer', 'items.product', 'deliveryAddress', 'vendor']);

        $customerName = $order->customer?->name ?? 'Valued Customer';
        $customerEmail = $order->customer?->email ?? 'N/A';
        $customerPhone = $order->customer?->phone ?? 'N/A';

        $vendorName = $order->vendor?->brand_name ?? 'FuelCab Direct';
        $vendorGstin = '27AAAAA1111A1Z1'; // Mock vendor GSTIN

        $deliveryAddress = $order->deliveryAddress?->full_address ?? 'N/A';

        $subtotal = (float) $order->subtotal_amount;
        $taxAmount = (float) $order->tax_amount;
        $cgst = round($taxAmount / 2, 2);
        $sgst = round($taxAmount / 2, 2);
        $delivery = (float) $order->delivery_fee;
        $total = (float) $order->total_amount;

        $invoiceContent = "========================================================================\n";
        $invoiceContent .= "                           TAX INVOICE / RECEIPT                        \n";
        $invoiceContent .= "========================================================================\n";
        $invoiceContent .= "Invoice Number : INV-{$order->id}\n";
        $invoiceContent .= 'Date           : '.now()->toFormattedDateString()."\n";
        $invoiceContent .= "Order Reference: {$order->id}\n";
        $invoiceContent .= 'Sales Channel  : '.strtoupper($order->channel instanceof \BackedEnum ? $order->channel->value : (string) $order->channel)."\n";
        $invoiceContent .= "------------------------------------------------------------------------\n";
        $invoiceContent .= "VENDOR DETAILS:\n";
        $invoiceContent .= "  Name         : {$vendorName}\n";
        $invoiceContent .= "  GSTIN        : {$vendorGstin}\n";
        $invoiceContent .= "  Support      : support@fuelcab.com\n";
        $invoiceContent .= "------------------------------------------------------------------------\n";
        $invoiceContent .= "CUSTOMER DETAILS:\n";
        $invoiceContent .= "  Name         : {$customerName}\n";
        $invoiceContent .= "  Email        : {$customerEmail}\n";
        $invoiceContent .= "  Phone        : {$customerPhone}\n";
        $invoiceContent .= "  Delivery To  : {$deliveryAddress}\n";
        $invoiceContent .= "========================================================================\n";
        $invoiceContent .= sprintf("%-30s %10s %12s %14s\n", 'PRODUCT DESCRIPTION', 'QTY', 'RATE (INR)', 'AMOUNT (INR)');
        $invoiceContent .= "------------------------------------------------------------------------\n";

        foreach ($order->items as $item) {
            $productName = $item->product_name_snapshot ?? $item->product?->name ?? 'Fuel Product';
            $qty = (float) $item->quantity;
            $rate = (float) $item->price_per_unit;
            $itemTotal = (float) $item->total_price;

            $unit = $item->unit_snapshot ?? 'L';

            $invoiceContent .= sprintf(
                "%-30s %10s %12s %14s\n",
                substr($productName, 0, 30),
                number_format($qty, 2).' '.$unit,
                '₹'.number_format($rate, 2),
                '₹'.number_format($itemTotal, 2)
            );
        }

        $invoiceContent .= "========================================================================\n";
        $invoiceContent .= sprintf("%-45s %25s\n", 'SUBTOTAL (Exclusive of Taxes)', '₹'.number_format($subtotal, 2));
        $invoiceContent .= sprintf("%-45s %25s\n", 'CGST (9%)', '₹'.number_format($cgst, 2));
        $invoiceContent .= sprintf("%-45s %25s\n", 'SGST (9%)', '₹'.number_format($sgst, 2));
        $invoiceContent .= sprintf("%-45s %25s\n", 'LOGISTICS & DELIVERY FEE', '₹'.number_format($delivery, 2));
        $invoiceContent .= "------------------------------------------------------------------------\n";
        $invoiceContent .= sprintf("%-45s %25s\n", 'GRAND TOTAL (Inclusive of GST)', '₹'.number_format($total, 2));
        $invoiceContent .= "========================================================================\n";
        $invoiceContent .= 'Payment Method : '.strtoupper($order->payment_method ?? 'online')."\n";
        $invoiceContent .= "Payment Status : COMPLETED / SUCCESSFUL\n";
        $invoiceContent .= "========================================================================\n";
        $invoiceContent .= "                Thank you for choosing FuelCab!                         \n";
        $invoiceContent .= "========================================================================\n";

        // Save file locally under public storage directory
        $relativePath = "invoices/{$order->id}.pdf";
        Storage::disk('public')->put($relativePath, $invoiceContent);

        return Storage::disk('public')->path($relativePath);
    }
}
