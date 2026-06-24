<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->string('type', 50); // credit, debit
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->uuid('reference_id')->nullable(); // Payment / Order UUID
            $table->string('reference_type', 100)->nullable(); // 'payment', 'order', 'topup', 'refund'

            // Timestamps only — wallet txn are immutable records
            $table->timestamps();

            // Foreign Keys
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');

            // Indexes
            $table->index('wallet_id');
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
