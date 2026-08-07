<?php

declare(strict_types=1);

namespace App\Modules\Auth\Listeners;

use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\WelcomeMail;
use App\Enums\UserRole;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(UserRegistered $event): void
    {
        try {
            $user = $event->user;
            $role = $user->role_type === UserRole::VendorAdmin ? 'vendor' : 'customer';

            if ($user->email) {
                SendEmailJob::dispatch($user->email, new WelcomeMail($user->name, $role));
            }
        } catch (\Throwable $e) {
            Log::error('[SendWelcomeNotification] Failed to queue welcome email', [
                'user_id' => $event->user->id ?? null,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
