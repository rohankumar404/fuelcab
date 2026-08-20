<?php

declare(strict_types=1);

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Models\PushToken;
use App\Modules\Notification\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public readonly string|array $targets,
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = []
    ) {
        $this->onQueue('default');
    }

    public function handle(FcmService $fcmService): void
    {
        $tokens = [];
        $targets = is_array($this->targets) ? $this->targets : [$this->targets];

        foreach ($targets as $target) {
            if (empty($target)) {
                continue;
            }

            // If the target is a UUID (User ID), resolve it to active tokens
            if (Str::isUuid($target)) {
                $userTokens = PushToken::where('user_id', $target)
                    ->where('is_active', true)
                    ->pluck('token')
                    ->toArray();
                $tokens = array_merge($tokens, $userTokens);
            } else {
                // Otherwise, treat it directly as an FCM token
                $tokens[] = $target;
            }
        }

        $tokens = array_values(array_unique(array_filter($tokens)));

        if (empty($tokens)) {
            Log::info('[SendPushNotificationJob] No active FCM tokens resolved for targets.', [
                'targets' => $this->targets,
            ]);

            return;
        }

        $success = $fcmService->sendNotification($tokens, $this->title, $this->body, $this->data);

        if (! $success) {
            Log::warning('[SendPushNotificationJob] Push notification dispatch returned failure state.', [
                'targets' => $this->targets,
                'attempt' => $this->attempts(),
            ]);

            // Throw exception to trigger Laravel's retry/backoff mechanism
            throw new \RuntimeException("FCM push notification delivery failed on attempt {$this->attempts()}.");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendPushNotificationJob] Push notification job failed all retries.', [
            'targets' => $this->targets,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
