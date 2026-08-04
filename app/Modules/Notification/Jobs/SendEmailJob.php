<?php

declare(strict_types=1);

namespace App\Modules\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    /**
     * Create a new job instance.
     *
     * @param string $recipient
     * @param Mailable $mailable
     */
    public function __construct(
        public readonly string $recipient,
        public readonly Mailable $mailable
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Don't send if emails are globally disabled in config
        if (! config('fuelcab.notifications.email.enabled', true)) {
            Log::info('[SendEmailJob] Mail dispatch skipped (globally disabled in config).', [
                'recipient' => $this->recipient,
                'mailable'  => get_class($this->mailable),
            ]);
            return;
        }

        Mail::to($this->recipient)->send($this->mailable);

        Log::info('[SendEmailJob] Email sent successfully.', [
            'recipient' => $this->recipient,
            'mailable'  => get_class($this->mailable),
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[SendEmailJob] Email job failed after max attempts.', [
            'recipient' => $this->recipient,
            'mailable'  => get_class($this->mailable),
            'error'     => $exception->getMessage(),
        ]);
    }
}
