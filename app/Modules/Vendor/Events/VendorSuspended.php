<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorSuspended
{
    use Dispatchable;
    use SerializesModels;

    public function __construct()
    {
        //
    }
}
