<?php

declare(strict_types=1);

namespace App\Modules\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly \App\Modules\Payment\Models\Payment $payment,
        public readonly ?string $errorMessage = null
    ) {}
}
