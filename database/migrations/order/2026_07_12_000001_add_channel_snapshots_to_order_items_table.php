<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            // Channel at time of order — immutable historical record
            $table->string('sales_channel', 50)->default('direct')->after('total_price')->index();

            // Vendor who fulfilled — null for Direct orders
            $table->uuid('vendor_id')->nullable()->after('sales_channel');

            // Immutable snapshots — product data may change, orders must not
            $table->string('product_name_snapshot')->nullable()->after('vendor_id');
            $table->string('product_sku_snapshot', 100)->nullable()->after('product_name_snapshot');
            $table->string('unit_snapshot', 50)->nullable()->after('product_sku_snapshot');

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropColumn([
                'sales_channel',
                'vendor_id',
                'product_name_snapshot',
                'product_sku_snapshot',
                'unit_snapshot',
            ]);
        });
    }
};
