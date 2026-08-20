<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Actions;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\VendorApprovedMail;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveVendorAction
{
    /**
     * Approve a vendor and notify their admin users.
     *
     * @throws \DomainException if vendor is not in a pending/suspended state.
     */
    public function execute(string $vendorId, string $approvedBy): Vendor
    {
        return DB::transaction(function () use ($vendorId, $approvedBy): Vendor {
            $vendor = Vendor::findOrFail($vendorId);

            if ($vendor->status === VendorStatus::Approved) {
                throw new \DomainException("Vendor [{$vendorId}] is already approved.");
            }

            $vendor->update([
                'status' => VendorStatus::Approved,
                'approved_at' => now(),
                'approved_by' => $approvedBy,
            ]);

            Log::info('[ApproveVendorAction] Vendor approved.', [
                'vendor_id' => $vendorId,
                'approved_by' => $approvedBy,
            ]);

            // Notify vendor admin users via email
            $vendor->users()
                ->where('role_type', 'vendor_admin')
                ->get()
                ->each(function ($user) use ($vendor): void {
                    SendEmailJob::dispatch($user->email, new VendorApprovedMail($user->name, $vendor->name));
                });

            return $vendor->fresh();
        });
    }
}
