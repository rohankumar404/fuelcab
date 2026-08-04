<?php

declare(strict_types=1);

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Services\AuthkeySmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Attempt delivery 3 times before marking failed. */
    public int $tries = 3;

    /** Seconds between first retry (exponential backoff handled manually in failed()). */
    public int $backoff = 5;

    /** Timeout per attempt (seconds). */
    public int $timeout = 30;

    public function __construct(
        public readonly string $phone,
        public readonly string $code,
    ) {
        $this->onQueue('sms');
    }

    /**
     * Dispatch the OTP to the phone via Authkey.io.
     */
    public function handle(AuthkeySmsService $smsService): void
    {
        $sent = $smsService->sendOtp($this->phone, $this->code);

        if (! $sent) {
            Log::warning('[SendSmsJob] Delivery failed — will retry if attempts remain', [
                'phone'   => $this->phone,
                'attempt' => $this->attempts(),
            ]);

            // Throw to trigger Laravel's retry / backoff mechanism
            $this->fail(new \RuntimeException(
                "Authkey SMS delivery to [{$this->phone}] failed on attempt {$this->attempts()}."
            ));
        }
    }

    /**
     * Called after all retries have been exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[SendSmsJob] All retries exhausted — OTP not delivered', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}
