<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletToppedUp
{
    use Dispatchable;
    use SerializesModels;

    public function __construct()
    {
        //
    }
}
