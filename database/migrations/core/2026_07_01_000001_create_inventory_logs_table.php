<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->uuid('product_id');
            $table->uuid('vendor_id');
            $table->string('type', 50); // increment, decrement, adjustment, sync
            $table->decimal('quantity_before', 12, 2)->default(0);
            $table->decimal('quantity_changed', 12, 2);
            $table->decimal('quantity_after', 12, 2);
            $table->string('reference_type', 100)->nullable(); // order, manual, api_sync
            $table->uuid('reference_id')->nullable();
            $table->text('notes')->nullable();

            // Audit
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            // Indexes
            $table->index(['product_id', 'created_at']);
            $table->index(['inventory_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
