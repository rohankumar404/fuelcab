<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('payment_id');
            $table->uuid('requested_by')->nullable(); // User UUID
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable();
            $table->string('status', 50)->default('pending'); // pending, approved, rejected, processed
            $table->string('gateway_refund_id', 255)->unique()->nullable();
            $table->timestamp('processed_at')->nullable();

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('restrict');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
