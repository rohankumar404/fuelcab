<?php

declare(strict_types=1);

namespace App\Modules\Driver\Listeners;

use App\Modules\Driver\Events\DriverLocationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BroadcastLocationToCustomer implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(DriverLocationUpdated $event): void
    {
        $location = $event->location;

        Log::info('[BroadcastLocationToCustomer] Driver location update received', [
            'driver_id' => $location->driverId,
            'lat' => $location->latitude,
            'lng' => $location->longitude,
            'order_id' => $location->orderId,
        ]);

        // When a Pusher / WebSockets channel is configured, broadcast here.
        // Example for future implementation:
        //
        // if ($location->orderId) {
        //     broadcast(new \App\Events\DriverPositionUpdated(
        //         orderId:  $location->orderId,
        //         lat:      $location->latitude,
        //         lng:      $location->longitude,
        //         heading:  $location->heading,
        //         speedKmh: $location->speedKmh
        //     ))->toOthers();
        // }
    }
}
