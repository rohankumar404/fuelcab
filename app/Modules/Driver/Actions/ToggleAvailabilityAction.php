<?php

declare(strict_types=1);

namespace App\Modules\Driver\Actions;

use App\Exceptions\ApiException;
use App\Modules\Driver\Models\Driver;

class ToggleAvailabilityAction
{
    /**
     * Toggle availability status of a driver.
     *
     * Valid statuses: offline, available, on_trip, suspended.
     */
    public function execute(string $userId, string $status): Driver
    {
        $driver = Driver::where('user_id', $userId)->firstOrFail();

        if (! $driver->is_approved) {
            throw new ApiException('Cannot change availability. Driver account is not approved yet.', 403);
        }

        if (! in_array($status, ['offline', 'available', 'on_trip', 'suspended'], true)) {
            throw new ApiException('Invalid driver status value.', 422);
        }

        $driver->update([
            'status' => $status,
        ]);

        return $driver;
    }
}
