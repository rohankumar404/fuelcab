<?php

declare(strict_types=1);

namespace App\Modules\Auth\Listeners;

use App\Modules\Auth\Events\OtpRequested;
use App\Modules\Notification\Jobs\SendSmsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Listens for OtpRequested events and queues an SMS delivery job.
 *
 * This listener itself is queued (ShouldQueue) so event dispatch
 * is non-blocking from the HTTP request cycle.
 */
class SendOtpViaSms implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OtpRequested $event): void
    {
        SendSmsJob::dispatch($event->phone, $event->code);
    }
}
