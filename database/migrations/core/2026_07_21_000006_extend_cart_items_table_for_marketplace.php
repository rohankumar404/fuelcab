<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('cart_items', 'vendor_listing_id')) {
                $table->uuid('vendor_listing_id')->nullable()->after('product_id');
                $table->foreign('vendor_listing_id')->references('id')->on('vendor_listings')->onDelete('set null');
                $table->index('vendor_listing_id');
            }

            if (! Schema::hasColumn('cart_items', 'product_sku_snapshot')) {
                $table->string('product_sku_snapshot', 100)->nullable()->after('product_name_snapshot');
            }

            if (! Schema::hasColumn('cart_items', 'unit_snapshot')) {
                $table->string('unit_snapshot', 50)->nullable()->after('product_sku_snapshot');
            }
        });

        // Make product_id nullable so cart items can reference vendor_listing_id directly
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->uuid('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            if (Schema::hasColumn('cart_items', 'vendor_listing_id')) {
                $table->dropForeign(['vendor_listing_id']);
                $table->dropColumn(['vendor_listing_id', 'product_sku_snapshot', 'unit_snapshot']);
            }
        });
    }
};
