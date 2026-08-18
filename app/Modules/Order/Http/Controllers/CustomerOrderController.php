<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\OrderSubscription;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Helpers\InvoicePdfGenerator;
use App\Modules\Vendor\Models\VendorListing;
use App\Enums\SalesChannel;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerOrderController extends Controller
{
    use ApiResponse;

    // ── Emergency Orders ─────────────────────────────────────────────────────

    public function createEmergencyOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_listing_id'   => 'required|uuid|exists:vendor_listings,id',
            'delivery_address_id' => 'required|uuid|exists:addresses,id',
            'quantity'            => 'required|numeric|min:1',
        ]);

        $listing = VendorListing::findOrFail($validated['vendor_listing_id']);
        $address = Address::findOrFail($validated['delivery_address_id']);
        $quantity = (float)$validated['quantity'];

        $subtotal = $listing->base_price * $quantity;
        $emergencyFee = 250.00; // Flat extra fee for priority slot bypass
        $tax = $subtotal * 0.18; // 18% standard GST
        $total = $subtotal + $emergencyFee + $tax;

        $order = DB::transaction(function () use ($request, $listing, $address, $quantity, $subtotal, $emergencyFee, $tax, $total) {
            // Ensure first party product exists to satisfy foreign key constraint on order_items
            $product = \App\Modules\Fuel\Models\Product::firstOrCreate(
                ['slug' => $listing->slug],
                [
                    'category_id'        => $listing->marketplaceProduct?->category_id ?? \App\Models\Category::first()?->id,
                    'vendor_id'          => $listing->vendor_id,
                    'name'               => $listing->listing_title,
                    'sku'                => $listing->sku,
                    'price_per_unit'     => $listing->base_price,
                    'unit_of_measure'    => \App\Enums\UnitOfMeasure::Litres,
                    'is_active'          => true,
                    'ordering_enabled'   => true,
                    'min_order_quantity' => 1.0,
                ]
            );

            $order = Order::create([
                'customer_id'         => $request->user()->id,
                'vendor_id'           => $listing->vendor_id,
                'delivery_address_id' => $address->id,
                'status'              => OrderStatus::Pending,
                'channel'             => SalesChannel::Direct,
                'subtotal_amount'     => $subtotal,
                'delivery_fee'        => $emergencyFee, 
                'tax_amount'          => $tax,
                'total_amount'        => $total,
                'is_emergency'        => true,
                'emergency_fee'       => $emergencyFee,
                'order_number'        => 'ORD-EMG-' . strtoupper(Str::random(6)),
            ]);

            OrderItem::create([
                'order_id'              => $order->id,
                'product_id'            => $product->id,
                'quantity'              => $quantity,
                'price_per_unit'        => $listing->base_price,
                'total_price'           => $subtotal,
                'sales_channel'         => SalesChannel::Direct,
                'vendor_id'             => $listing->vendor_id,
                'product_name_snapshot' => $listing->listing_title,
                'product_sku_snapshot'  => $listing->sku,
                'unit_snapshot'         => 'Litres',
            ]);

            return $order;
        });

        return $this->success($order, 'Emergency order placed successfully.', 201);
    }

    // ── Subscription Orders ──────────────────────────────────────────────────

    public function listSubscriptions(Request $request): JsonResponse
    {
        $subscriptions = OrderSubscription::with('listing.marketplaceProduct')
            ->where('user_id', $request->user()->id)
            ->get();

        return $this->success($subscriptions, 'Subscriptions retrieved successfully.');
    }

    public function createSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_listing_id' => 'required|uuid|exists:vendor_listings,id',
            'quantity'          => 'required|numeric|min:1',
            'frequency'         => 'required|in:daily,weekly,monthly',
        ]);

        $nextDelivery = match ($validated['frequency']) {
            'daily'   => now()->addDay(),
            'weekly'  => now()->addWeek(),
            'monthly' => now()->addMonth(),
        };

        $subscription = OrderSubscription::create([
            'user_id'           => $request->user()->id,
            'vendor_listing_id' => $validated['vendor_listing_id'],
            'quantity'          => $validated['quantity'],
            'frequency'         => $validated['frequency'],
            'status'            => 'active',
            'next_delivery_at'  => $nextDelivery,
        ]);

        return $this->success($subscription, 'Subscription created successfully.', 201);
    }

    public function updateSubscription(Request $request, string $id): JsonResponse
    {
        $subscription = OrderSubscription::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'quantity'  => 'sometimes|required|numeric|min:1',
            'frequency' => 'sometimes|required|in:daily,weekly,monthly',
            'status'    => 'sometimes|required|in:active,paused,cancelled',
        ]);

        if (isset($validated['frequency']) && $validated['frequency'] !== $subscription->frequency) {
            $validated['next_delivery_at'] = match ($validated['frequency']) {
                'daily'   => now()->addDay(),
                'weekly'  => now()->addWeek(),
                'monthly' => now()->addMonth(),
            };
        }

        $subscription->update($validated);

        return $this->success($subscription, 'Subscription updated successfully.');
    }

    public function cancelSubscription(Request $request, string $id): JsonResponse
    {
        $subscription = OrderSubscription::where('user_id', $request->user()->id)->findOrFail($id);
        $subscription->update(['status' => 'cancelled']);

        return $this->success($subscription, 'Subscription cancelled successfully.');
    }

    // ── Invoices ─────────────────────────────────────────────────────────────

    public function downloadInvoice(Request $request, string $orderId): BinaryFileResponse|JsonResponse
    {
        $order = Order::where('customer_id', $request->user()->id)->findOrFail($orderId);

        $generator = new InvoicePdfGenerator();
        $filePath = $generator->generate($order);

        if (!file_exists($filePath)) {
            return $this->error('Invoice file not found.', null, 404);
        }

        return response()->download($filePath, "invoice_{$order->order_number}.txt");
    }
}
