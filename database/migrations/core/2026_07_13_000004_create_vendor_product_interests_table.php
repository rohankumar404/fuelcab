<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_marketplace_products', function (Blueprint $table): void {
            $table->uuid('vendor_id');
            $table->uuid('marketplace_product_id');
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('marketplace_product_id')->references('id')->on('marketplace_products')->onDelete('cascade');

            $table->unique(['vendor_id', 'marketplace_product_id'], 'idx_vendor_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_marketplace_products');
    }
};
