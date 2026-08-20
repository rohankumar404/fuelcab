<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Listeners;

use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\VendorApprovedMail;
use App\Modules\Vendor\Events\VendorApproved;
use App\Modules\Vendor\Notifications\VendorApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendVendorApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(VendorApproved $event): void
    {
        try {
            $vendor = $event->vendor;

            if ($vendor->email) {
                SendEmailJob::dispatch(
                    $vendor->email,
                    new VendorApprovedMail($vendor->contact_person, $vendor->brand_name, $vendor->vendor_code)
                );
            }

            // Notify all vendor users (database + FCM)
            $vendor->loadMissing('users');
            foreach ($vendor->users as $user) {
                $user->notify(new VendorApprovedNotification($vendor));
            }
        } catch (\Throwable $e) {
            Log::error('[SendVendorApprovalNotification] Failed to queue vendor approval email', [
                'vendor_id' => $event->vendor->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
