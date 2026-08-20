<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Services;

use App\Modules\Wallet\Models\Wallet;
use App\Modules\Wallet\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WalletService
{
    /**
     * Get or create a wallet for the given user.
     */
    public function getOrCreateWallet(string $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0.00, 'currency' => 'INR']
        );
    }

    /**
     * Credit (add) funds to a user's wallet.
     *
     * @param  float  $amount  Positive amount to add.
     * @param  string  $description  Human-readable description.
     * @param  string|null  $referenceId  UUID of the related model (order, payment, etc.).
     * @param  string|null  $referenceType  Morph type string.
     */
    public function credit(
        string $userId,
        float $amount,
        string $description = 'Credit',
        ?string $referenceId = null,
        ?string $referenceType = null,
    ): WalletTransaction {
        if ($amount <= 0.0) {
            throw new RuntimeException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($userId, $amount, $description, $referenceId, $referenceType): WalletTransaction {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0.00, 'currency' => 'INR']
            );

            $before = (float) $wallet->balance;
            $after = $before + $amount;

            $wallet->update(['balance' => $after]);

            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);

            Log::info('[WalletService] Credited wallet', [
                'user_id' => $userId,
                'amount' => $amount,
                'balance' => $after,
                'tx_id' => $tx->id,
                'reference' => "{$referenceType}:{$referenceId}",
            ]);

            return $tx;
        });
    }

    /**
     * Debit (deduct) funds from a user's wallet.
     *
     * @throws RuntimeException if balance is insufficient.
     */
    public function debit(
        string $userId,
        float $amount,
        string $description = 'Debit',
        ?string $referenceId = null,
        ?string $referenceType = null,
    ): WalletTransaction {
        if ($amount <= 0.0) {
            throw new RuntimeException('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($userId, $amount, $description, $referenceId, $referenceType): WalletTransaction {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            $before = (float) $wallet->balance;

            if ($before < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $after = $before - $amount;
            $wallet->update(['balance' => $after]);

            $tx = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);

            Log::info('[WalletService] Debited wallet', [
                'user_id' => $userId,
                'amount' => $amount,
                'balance' => $after,
                'tx_id' => $tx->id,
                'reference' => "{$referenceType}:{$referenceId}",
            ]);

            return $tx;
        });
    }

    /**
     * Get the current balance for a user.
     */
    public function balance(string $userId): float
    {
        return (float) Wallet::where('user_id', $userId)->value('balance') ?? 0.0;
    }
}
