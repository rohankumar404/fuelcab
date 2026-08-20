<?php

declare(strict_types=1);

namespace App\Modules\Driver\Events;

use App\Modules\Driver\Models\Driver;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Driver $driver
    ) {}
}
