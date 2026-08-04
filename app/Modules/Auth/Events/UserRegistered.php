<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}
}
