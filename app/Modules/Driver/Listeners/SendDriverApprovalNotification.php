<?php

declare(strict_types=1);

namespace App\Modules\Driver\Listeners;

use App\Modules\Driver\Events\DriverApproved;
use App\Modules\Driver\Notifications\DriverApprovedNotification;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\VendorApprovedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendDriverApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public int $tries = 3;

    public function handle(DriverApproved $event): void
    {
        $driver = $event->driver->load('user');

        if (! $driver->user || ! $driver->user->email) {
            Log::warning('[SendDriverApprovalNotification] Driver has no user email — skipping.', [
                'driver_id' => $driver->id,
            ]);

            return;
        }

        // Notify driver user via email
        SendEmailJob::dispatch(
            $driver->user->email,
            new VendorApprovedMail($driver->user->name, 'Driver Account')
        );

        // Notify via push/database notification
        $driver->user->notify(new DriverApprovedNotification($driver));

        Log::info('[SendDriverApprovalNotification] Driver approval notification dispatched.', [
            'driver_id' => $driver->id,
            'user_id' => $driver->user_id,
        ]);
    }

    public function failed(DriverApproved $event, Throwable $exception): void
    {
        Log::error('[SendDriverApprovalNotification] Failed to send driver approval notification.', [
            'driver_id' => $event->driver->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
