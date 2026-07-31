<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_listings', 'product_image')) {
                $table->string('product_image', 2048)->nullable()->after('product_images');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_listings', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_listings', 'product_image')) {
                $table->dropColumn('product_image');
            }
        });
    }
};
