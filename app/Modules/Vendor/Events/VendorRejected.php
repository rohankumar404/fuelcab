<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Events;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Vendor $vendor,
        public readonly string $reason
    ) {}
}
