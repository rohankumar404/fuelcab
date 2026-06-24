<?php

declare(strict_types=1);

namespace App\Modules\Auth\Listeners;

use App\Modules\Auth\Events\OtpRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOtpViaSms implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OtpRequested $event): void
    {
        // TODO: Implement SendOtpViaSms.
    }
}
