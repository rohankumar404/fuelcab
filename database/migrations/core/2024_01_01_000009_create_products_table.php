<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->uuid('vendor_id');
            $table->string('name');
            $table->string('slug');
            $table->string('sku', 100);
            $table->text('description')->nullable();
            $table->decimal('price_per_unit', 12, 4);
            $table->string('unit_of_measure', 50)->default('liter');
            $table->boolean('is_active')->default(true);

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            // Unique sku per vendor
            $table->unique(['vendor_id', 'sku']);

            // Indexes
            $table->index(['vendor_id', 'category_id']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
