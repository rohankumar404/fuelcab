<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Models\Address;
use App\Modules\Cart\Models\Cart;
use App\Modules\Checkout\Models\Checkout;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\DB;

class UpdateCheckoutAddressAction
{
    public function execute(string $userId, string $checkoutId, string $addressId): Checkout
    {
        return DB::transaction(function () use ($userId, $checkoutId, $addressId) {
            $checkout = Checkout::with('cart.items')
                ->where('user_id', $userId)
                ->where('status', 'draft')
                ->findOrFail($checkoutId);

            $address = Address::where('user_id', $userId)->findOrFail($addressId);

            // ── Resolve vendor for radius check ───────────────────────────
            // For a mixed cart, use the first vendor found.
            // For a cart with no vendor (edge case), use a fallback.
            $vendorId = $checkout->vendor_id
                ?? $checkout->cart?->items?->whereNotNull('vendor_id')->first()?->vendor_id;

            $deliveryFee = 150.00; // Default fee if no vendor context

            if ($vendorId) {
                $vendor = Vendor::find($vendorId);

                if ($vendor) {
                    // Find vendor depot address for distance calc
                    $vendorAddress = Address::where('company_id', $vendor->company_id)->first();

                    if ($vendorAddress) {
                        $distance = $this->calculateDistance(
                            (float) $address->latitude,
                            (float) $address->longitude,
                            (float) $vendorAddress->latitude,
                            (float) $vendorAddress->longitude
                        );

                        // Only enforce radius check if vendor has service_radius_meters set
                        if ($vendor->service_radius_meters && $distance > $vendor->service_radius_meters) {
                            throw new \DomainException(
                                "Selected delivery address is outside the vendor's service radius of "
                                . ($vendor->service_radius_meters / 1000) . " KM."
                            );
                        }

                        // Delivery Fee: Base + distance rate
                        $baseLogistics = 150.00;
                        $ratePerKm     = 15.00;
                        $distanceKm    = $distance / 1000.00;
                        $deliveryFee   = round($baseLogistics + ($distanceKm * $ratePerKm), 2);
                    }
                }
            }

            $checkout->update([
                'address_id'   => $addressId,
                'delivery_fee' => $deliveryFee,
            ]);

            // Re-calculate grand total
            $checkout = (new CalculateCheckoutSummaryAction())->execute($userId, $checkoutId);

            return $checkout;
        });
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
