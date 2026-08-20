<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Actions;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\VendorRejectedMail;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuspendVendorAction
{
    /**
     * Suspend an active/approved vendor with a required reason.
     *
     * @throws \DomainException if vendor is already suspended or cannot be suspended.
     */
    public function execute(string $vendorId, string $reason, string $suspendedBy): Vendor
    {
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('A suspension reason is required.');
        }

        return DB::transaction(function () use ($vendorId, $reason, $suspendedBy): Vendor {
            $vendor = Vendor::findOrFail($vendorId);

            if ($vendor->status === VendorStatus::Suspended) {
                throw new \DomainException("Vendor [{$vendorId}] is already suspended.");
            }

            $vendor->update([
                'status' => VendorStatus::Suspended,
                'suspension_reason' => $reason,
                'suspended_at' => now(),
                'suspended_by' => $suspendedBy,
            ]);

            Log::info('[SuspendVendorAction] Vendor suspended.', [
                'vendor_id' => $vendorId,
                'suspended_by' => $suspendedBy,
                'reason' => $reason,
            ]);

            // Notify vendor admin users
            $vendor->users()
                ->where('role_type', 'vendor_admin')
                ->get()
                ->each(fn ($user) => SendEmailJob::dispatch($user->email, new VendorRejectedMail($user->name, $vendor->name, $reason)));

            return $vendor->fresh();
        });
    }
}
