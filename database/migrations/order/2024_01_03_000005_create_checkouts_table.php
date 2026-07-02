<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('cart_id');
            $table->uuid('address_id')->nullable();
            $table->uuid('vendor_id')->nullable();
            $table->timestamp('scheduled_delivery_at')->nullable();
            $table->string('status', 50)->default('draft'); // draft, completed, cancelled
            $table->decimal('subtotal_amount', 12, 2)->default(0.00);
            $table->decimal('delivery_fee', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('payment_method', 50)->nullable(); // razorpay, stripe, wallet
            $table->string('payment_status', 50)->default('pending'); // pending, success, failed

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');

            // Indexes
            $table->index('user_id');
            $table->index('cart_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
