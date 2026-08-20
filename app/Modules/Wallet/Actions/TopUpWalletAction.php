<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Actions;

use App\Modules\Wallet\Models\WalletTransaction;
use App\Modules\Wallet\Services\WalletService;

class TopUpWalletAction
{
    public function __construct(private readonly WalletService $walletService) {}

    /**
     * Top up a user's wallet.
     */
    public function execute(
        string $userId,
        float $amount,
        string $description = 'Top Up',
        ?string $referenceId = null,
        ?string $referenceType = null
    ): WalletTransaction {
        return $this->walletService->credit(
            userId: $userId,
            amount: $amount,
            description: $description,
            referenceId: $referenceId,
            referenceType: $referenceType
        );
    }
}
