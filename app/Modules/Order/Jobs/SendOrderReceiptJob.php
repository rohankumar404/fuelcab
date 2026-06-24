<?php

declare(strict_types=1);

namespace App\\Modules\\Order\\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderReceiptJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $queue = 'default';

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        // TODO: Implement SendOrderReceiptJob.
    }

    public function failed(\Throwable $exception): void
    {
        // TODO: Handle job failure.
    }
}
