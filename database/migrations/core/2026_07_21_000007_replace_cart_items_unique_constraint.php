<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the (cart_id, product_id) unique constraint with a smarter
 * partial unique index:
 *  - For direct products:    unique on (cart_id, product_id)       where vendor_listing_id IS NULL
 *  - For marketplace items:  unique on (cart_id, vendor_listing_id) where vendor_listing_id IS NOT NULL
 *
 * SQLite (used in tests) does not support partial indexes via Blueprint,
 * so we use raw SQL for the partial index and a standard Blueprint index
 * for the PostgreSQL production constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            // 1. Drop the old (cart_id, product_id) unique constraint
            $table->dropUnique(['cart_id', 'product_id']);

            // 2. Drop FK on product_id so we can allow NULL
            // (already done by the previous migration that set nullable)
        });

        // 3. Add a non-partial unique index for (cart_id, product_id)
        //    that only applies when product_id IS NOT NULL.
        // In SQLite/PostgreSQL we use a raw partial unique index.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS cart_items_cart_product_unique
                ON cart_items (cart_id, product_id)
                WHERE product_id IS NOT NULL AND vendor_listing_id IS NULL');

            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS cart_items_cart_listing_unique
                ON cart_items (cart_id, vendor_listing_id)
                WHERE vendor_listing_id IS NOT NULL AND deleted_at IS NULL');
        } else {
            // PostgreSQL partial unique indexes
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS cart_items_cart_product_unique
                ON cart_items (cart_id, product_id)
                WHERE product_id IS NOT NULL AND vendor_listing_id IS NULL AND deleted_at IS NULL');

            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS cart_items_cart_listing_unique
                ON cart_items (cart_id, vendor_listing_id)
                WHERE vendor_listing_id IS NOT NULL AND deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        // Drop partial indexes
        DB::statement('DROP INDEX IF EXISTS cart_items_cart_product_unique');
        DB::statement('DROP INDEX IF EXISTS cart_items_cart_listing_unique');

        // Restore original unique constraint
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->unique(['cart_id', 'product_id']);
        });
    }
};
