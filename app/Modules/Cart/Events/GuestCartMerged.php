<?php

declare(strict_types=1);

namespace App\Modules\Cart\Events;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestCartMerged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Cart $userCart,
        public readonly User $user,
        public readonly int  $itemsMerged,
    ) {}
}
