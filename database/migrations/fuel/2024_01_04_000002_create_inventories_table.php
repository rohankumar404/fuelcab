<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('product_id')->unique();
            $table->uuid('vendor_id');
            $table->decimal('quantity_available', 12, 2)->default(0.00);
            $table->decimal('low_stock_threshold', 12, 2)->default(100.00);
            $table->timestamp('last_restocked_at')->nullable();

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            // Indexes
            $table->index(['vendor_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
