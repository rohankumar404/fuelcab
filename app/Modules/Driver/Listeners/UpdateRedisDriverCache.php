<?php

declare(strict_types=1);

namespace App\Modules\Driver\Listeners;

use App\Modules\Driver\Events\DriverLocationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class UpdateRedisDriverCache implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(DriverLocationUpdated $event): void
    {
        try {
            $locationData = $event->location->toArray();
            
            // 1. Set in hash map for quick lookups
            Redis::hset('driver_locations', $event->location->driverId, json_encode($locationData));
            
            // 2. Set an individual key with 30-minute expiry for driver tracking presence
            Redis::setex('driver_presence:' . $event->location->driverId, 1800, json_encode($locationData));
        } catch (\Throwable $e) {
            Log::warning('[UpdateRedisDriverCache] Redis failed to cache driver location. Falling back to DB only.', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
