<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Modules\Checkout\Models\Checkout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateCheckoutScheduleAction
{
    public function execute(string $userId, string $checkoutId, string $scheduledAt): Checkout
    {
        return DB::transaction(function () use ($userId, $checkoutId, $scheduledAt) {
            $checkout = Checkout::where('user_id', $userId)->where('status', 'draft')->findOrFail($checkoutId);

            $dateTime = Carbon::parse($scheduledAt);

            if ($dateTime->isPast()) {
                throw new \DomainException("Scheduled delivery time must be in the future.");
            }

            // Ensure scheduled slot is within reasonable operating hours (e.g. 7 AM to 10 PM)
            $hour = $dateTime->hour;
            if ($hour < 7 || $hour > 22) {
                throw new \DomainException("Delivery slots are only available between 07:00 AM and 10:00 PM.");
            }

            $checkout->update([
                'scheduled_delivery_at' => $dateTime,
            ]);

            return $checkout;
        });
    }
}
