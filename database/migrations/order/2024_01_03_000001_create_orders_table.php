<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->uuid('vendor_id');
            $table->uuid('driver_id')->nullable();
            $table->uuid('delivery_address_id');
            $table->string('status', 50)->default('pending');
            $table->decimal('subtotal_amount', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->timestamp('scheduled_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('delivery_address_id')->references('id')->on('addresses')->onDelete('restrict');

            // Indexes
            $table->index('customer_id');
            $table->index('vendor_id');
            $table->index('driver_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
