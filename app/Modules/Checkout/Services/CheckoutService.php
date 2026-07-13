<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Services;

use App\Modules\Checkout\Actions\InitializeCheckoutAction;
use App\Modules\Checkout\Actions\UpdateCheckoutAddressAction;
use App\Modules\Checkout\Actions\UpdateCheckoutScheduleAction;
use App\Modules\Checkout\Actions\CalculateCheckoutSummaryAction;
use App\Modules\Checkout\Actions\ProcessCheckoutPaymentAction;
use App\Modules\Checkout\DTOs\CheckoutResultDTO;
use App\Modules\Checkout\Models\Checkout;

class CheckoutService
{
    public function __construct(
        private readonly InitializeCheckoutAction        $initializeCheckout,
        private readonly UpdateCheckoutAddressAction     $updateAddress,
        private readonly UpdateCheckoutScheduleAction    $updateSchedule,
        private readonly CalculateCheckoutSummaryAction  $calculateSummary,
        private readonly ProcessCheckoutPaymentAction    $processPayment,
    ) {}

    public function initialize(string $userId, string $cartId): Checkout
    {
        return $this->initializeCheckout->execute($userId, $cartId);
    }

    public function selectAddress(string $userId, string $checkoutId, string $addressId): Checkout
    {
        return $this->updateAddress->execute($userId, $checkoutId, $addressId);
    }

    public function selectSchedule(string $userId, string $checkoutId, string $scheduledAt): Checkout
    {
        return $this->updateSchedule->execute($userId, $checkoutId, $scheduledAt);
    }

    public function getSummary(string $userId, string $checkoutId): Checkout
    {
        return $this->calculateSummary->execute($userId, $checkoutId);
    }

    /**
     * Process payment and create all fulfillment orders.
     *
     * Returns a CheckoutResultDTO containing:
     *   - $result->orders   : Collection of all created Orders (one per fulfillment group)
     *   - $result->payment  : The parent Payment record
     *   - $result->primaryOrder() : First order (for backward-compatible callers)
     */
    public function pay(string $userId, string $checkoutId, string $paymentMethod): CheckoutResultDTO
    {
        return $this->processPayment->execute($userId, $checkoutId, $paymentMethod);
    }
}
