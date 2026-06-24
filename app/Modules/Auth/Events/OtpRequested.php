<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtpRequested
{
    use Dispatchable;
    use SerializesModels;

    public function __construct()
    {
        //
    }
}
