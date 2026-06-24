<?php

declare(strict_types=1);

namespace App\Modules\Driver\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct()
    {
        //
    }
}
