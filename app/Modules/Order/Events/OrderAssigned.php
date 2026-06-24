<?php

declare(strict_types=1);

namespace App\Modules\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct()
    {
        //
    }
}
