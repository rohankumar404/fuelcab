<?php

declare(strict_types=1);

namespace App\Modules\Driver\Listeners;

use App\Modules\Driver\Events\DriverLocationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BroadcastLocationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(DriverLocationUpdated $event): void
    {
        // TODO: Implement BroadcastLocationToCustomer.
    }
}
