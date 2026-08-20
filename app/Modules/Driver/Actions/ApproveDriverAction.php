<?php

declare(strict_types=1);

namespace App\Modules\Driver\Actions;

use App\Modules\Driver\Events\DriverApproved;
use App\Modules\Driver\Models\Driver;

class ApproveDriverAction
{
    /**
     * Approve a driver and mark them offline (ready for service).
     */
    public function execute(string $driverId, string $approvedByUserId): Driver
    {
        $driver = Driver::findOrFail($driverId);

        $driver->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $approvedByUserId,
            'status' => 'offline',
        ]);

        event(new DriverApproved($driver));

        return $driver;
    }
}
