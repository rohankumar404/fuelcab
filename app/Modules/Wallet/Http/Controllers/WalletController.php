<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Models\Wallet;
use App\Modules\Wallet\Models\WalletTransaction;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['balance' => 0.00, 'currency' => 'INR']
        );

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return $this->success([
            'balance' => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'transactions' => $transactions,
        ], 'Wallet details retrieved successfully.');
    }

    public function topUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];

        $wallet = DB::transaction(function () use ($request, $amount, $validated) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $request->user()->id],
                ['balance' => 0.00, 'currency' => 'INR']
            );

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->update(['balance' => $balanceAfter]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $validated['description'] ?? 'Wallet Top-up',
                'reference_type' => 'topup',
                'reference_id' => Str::uuid()->toString(),
            ]);

            return $wallet;
        });

        return $this->success($wallet, 'Wallet topped up successfully.');
    }

    public function deduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];

        try {
            $wallet = DB::transaction(function () use ($request, $amount, $validated) {
                $wallet = Wallet::where('user_id', $request->user()->id)->first();

                if (! $wallet || (float) $wallet->balance < $amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }

                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore - $amount;

                $wallet->update(['balance' => $balanceAfter]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => $validated['description'] ?? 'Wallet Deduction',
                    'reference_type' => 'payment',
                    'reference_id' => Str::uuid()->toString(),
                ]);

                return $wallet;
            });

            return $this->success($wallet, 'Amount deducted successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 400);
        }
    }
}
