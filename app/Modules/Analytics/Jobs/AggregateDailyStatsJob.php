<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Jobs;

use App\Modules\Analytics\Services\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AggregateDailyStatsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct()
    {
        $this->queue = 'low';
    }

    public function handle(AnalyticsService $analyticsService): void
    {
        $stats = $analyticsService->aggregateDailyStats();

        Log::info('[AggregateDailyStatsJob] Daily statistics aggregated successfully.', [
            'date' => $stats['date'] ?? null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('[AggregateDailyStatsJob] Daily statistics aggregation failed.', [
            'error' => $exception->getMessage(),
        ]);
    }
}
