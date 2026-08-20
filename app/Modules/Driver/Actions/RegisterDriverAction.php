<?php

declare(strict_types=1);

namespace App\Modules\Driver\Actions;

use App\Models\User;
use App\Modules\Driver\Events\DriverRegistered;
use App\Modules\Driver\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterDriverAction
{
    /**
     * Register a user as a driver by creating a Driver profile.
     *
     * @param  string  $userId  UUID of the User to register as a driver.
     * @param  string  $licenseNumber  Driver's license number.
     * @param  string  $licenseExpiry  License expiry date (Y-m-d).
     * @param  string|null  $vendorId  UUID of the associated vendor (optional).
     */
    public function execute(
        string $userId,
        string $licenseNumber,
        string $licenseExpiry,
        ?string $vendorId = null,
    ): Driver {
        return DB::transaction(function () use ($userId, $licenseNumber, $licenseExpiry, $vendorId): Driver {
            $user = User::findOrFail($userId);

            $driver = Driver::create([
                'user_id' => $userId,
                'vendor_id' => $vendorId,
                'license_number' => $licenseNumber,
                'license_expiry' => $licenseExpiry,
                'status' => 'offline',
                'is_approved' => false,
            ]);

            Log::info('[RegisterDriverAction] Driver profile created.', [
                'user_id' => $userId,
                'driver_id' => $driver->id,
            ]);

            event(new DriverRegistered($driver));

            return $driver->fresh();
        });
    }
}
