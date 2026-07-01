<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Jobs;

use App\Modules\Fuel\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * @param array<int, array{product_id: string, quantity_available: float}> $items
     */
    public function __construct(
        private readonly array $items,
        private readonly string $referenceType = 'api_sync',
        private readonly ?string $referenceId = null,
    ) {}

    public function handle(ProductService $productService): void
    {
        foreach ($this->items as $item) {
            try {
                $productService->syncInventory(
                    $item['product_id'],
                    (float) $item['quantity_available'],
                    $this->referenceType,
                    $this->referenceId,
                );
            } catch (\Throwable $e) {
                Log::error('SyncInventoryJob: failed for product', [
                    'product_id' => $item['product_id'],
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
