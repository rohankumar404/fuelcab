<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Listeners;

use App\Modules\Vendor\Events\VendorRejected;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\VendorRejectedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendVendorRejectionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(VendorRejected $event): void
    {
        try {
            $vendor = $event->vendor;

            if ($vendor->email) {
                SendEmailJob::dispatch(
                    $vendor->email,
                    new VendorRejectedMail($vendor->contact_person, $vendor->brand_name, $event->reason)
                );
            }

            // Notify all vendor users (database + FCM)
            $vendor->loadMissing('users');
            foreach ($vendor->users as $user) {
                $user->notify(new \App\Modules\Vendor\Notifications\VendorRejectedNotification($vendor, $event->reason));
            }
        } catch (\Throwable $e) {
            Log::error('[SendVendorRejectionNotification] Failed to queue vendor rejection email', [
                'vendor_id' => $event->vendor->id ?? null,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
