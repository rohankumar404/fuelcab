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
            // Sales channel context for this line item
            $table->string('sales_channel', 50)->default('direct')->after('unit_of_measure')->index();

            // Vendor who fulfills this item (null = FuelCab Direct)
            $table->uuid('vendor_id')->nullable()->after('sales_channel');

            // Immutable product name captured at add-to-cart time
            $table->string('product_name_snapshot')->nullable()->after('vendor_id');

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['sales_channel', 'vendor_id', 'product_name_snapshot']);
        });
    }
};
