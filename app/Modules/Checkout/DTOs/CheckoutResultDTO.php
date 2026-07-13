<?php

declare(strict_types=1);

namespace App\Modules\Checkout\DTOs;

use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Collection;

/**
 * Returned by ProcessCheckoutPaymentAction.
 *
 * Contains all orders created from a single checkout session
 * (one per fulfillment group) and the parent payment record.
 */
final class CheckoutResultDTO
{
    /**
     * @param Collection<int, Order> $orders   All orders created (one per fulfillment group)
     * @param Payment                $payment  Single payment covering the full checkout amount
     */
    public function __construct(
        public readonly Collection $orders,
        public readonly Payment    $payment,
    ) {}

    /**
     * The first/primary order (for backward-compatible callers expecting a single Order).
     */
    public function primaryOrder(): Order
    {
        return $this->orders->first();
    }

    public function orderCount(): int
    {
        return $this->orders->count();
    }
}
