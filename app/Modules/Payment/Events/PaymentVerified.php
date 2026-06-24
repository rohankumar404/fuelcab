<?php

declare(strict_types=1);

namespace App\Modules\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerified
{
    use Dispatchable;
    use SerializesModels;

    public function __construct()
    {
        //
    }
}
