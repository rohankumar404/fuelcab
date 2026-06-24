<?php

declare(strict_types=1);

namespace App\Modules\Payment\Listeners;

use App\Modules\Payment\Events\PaymentVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateWalletBalance implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(PaymentVerified $event): void
    {
        // TODO: Implement UpdateWalletBalance.
    }
}
