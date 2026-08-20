<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Actions;

use App\Modules\Wallet\Models\WalletTransaction;
use App\Modules\Wallet\Services\WalletService;

class DeductWalletAction
{
    public function __construct(private readonly WalletService $walletService) {}

    /**
     * Deduct funds from a user's wallet.
     */
    public function execute(
        string $userId,
        float $amount,
        string $description = 'Deduction',
        ?string $referenceId = null,
        ?string $referenceType = null
    ): WalletTransaction {
        return $this->walletService->debit(
            userId: $userId,
            amount: $amount,
            description: $description,
            referenceId: $referenceId,
            referenceType: $referenceType
        );
    }
}
