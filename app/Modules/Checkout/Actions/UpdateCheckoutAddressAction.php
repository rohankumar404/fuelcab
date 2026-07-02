<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Models\Address;
use App\Modules\Checkout\Models\Checkout;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\DB;

class UpdateCheckoutAddressAction
{
    public function execute(string $userId, string $checkoutId, string $addressId): Checkout
    {
        return DB::transaction(function () use ($userId, $checkoutId, $addressId) {
            $checkout = Checkout::where('user_id', $userId)->where('status', 'draft')->findOrFail($checkoutId);
            $address = Address::where('user_id', $userId)->findOrFail($addressId);

            $vendorId = $checkout->vendor_id;
            if (! $vendorId) {
                throw new \DomainException("No vendor is selected for this checkout session.");
            }

            $vendor = Vendor::findOrFail($vendorId);

            // Find vendor location (company address)
            $vendorAddress = Address::where('company_id', $vendor->company_id)->first();
            if (! $vendorAddress) {
                // Fallback location if vendor has no company address set
                $vendorAddress = (object) [
                    'latitude'  => 12.9716,
                    'longitude' => 77.5946,
                ];
            }

            // Calculate distance using Haversine formula in meters
            $distance = $this->calculateDistance(
                (float) $address->latitude,
                (float) $address->longitude,
                (float) $vendorAddress->latitude,
                (float) $vendorAddress->longitude
            );

            if ($distance > $vendor->service_radius_meters) {
                throw new \DomainException("Selected delivery address is outside the vendor's service radius of " . ($vendor->service_radius_meters / 1000) . " KM.");
            }

            // Delivery Fee Formula: Base logistics fee + (distance in KM * Rate per KM)
            $baseLogistics = 150.00;
            $ratePerKm = 15.00;
            $distanceKm = $distance / 1000.00;
            $deliveryFee = $baseLogistics + ($distanceKm * $ratePerKm);

            $checkout->update([
                'address_id'   => $addressId,
                'delivery_fee' => round($deliveryFee, 2),
            ]);

            // Re-calculate the grand total with summary action
            $checkout = (new CalculateCheckoutSummaryAction())->execute($userId, $checkoutId);

            return $checkout;
        });
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // in meters

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
