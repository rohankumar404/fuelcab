<?php

declare(strict_types=1);

namespace App\Modules\Driver\Policies;

use App\Models\User;
use App\Modules\Driver\Models\Driver;
use Illuminate\Auth\Access\HandlesAuthorization;

class DriverPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any drivers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_drivers');
    }

    /**
     * Determine whether the user can view the driver profile.
     */
    public function view(User $user, Driver $driver): bool
    {
        if (!$user->can('view_drivers')) {
            return false;
        }

        // Driver viewing their own profile
        if ($user->hasRole('driver')) {
            return $user->id === $driver->user_id;
        }

        // Vendor admin viewing their own vendor drivers
        if ($user->hasRole('vendor_admin')) {
            return $user->vendor_id === $driver->vendor_id;
        }

        return true;
    }

    /**
     * Determine whether the user can approve/verify a driver.
     */
    public function approve(User $user): bool
    {
        return $user->can('approve_drivers');
    }

    /**
     * Determine whether the user can track a driver in real-time.
     */
    public function track(User $user, Driver $driver): bool
    {
        if (!$user->can('track_drivers')) {
            return false;
        }

        // Vendor admin tracking vendor's drivers
        if ($user->hasRole('vendor_admin')) {
            return $user->vendor_id === $driver->vendor_id;
        }

        return true;
    }
}
