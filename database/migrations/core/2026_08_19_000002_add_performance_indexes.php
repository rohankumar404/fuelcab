<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add unique composite index on vendor_ratings
        Schema::table('vendor_ratings', function (Blueprint $table) {
            $table->unique(['user_id', 'vendor_id'], 'idx_vendor_ratings_user_vendor_unique');
        });

        // 2. Add foreign key index on order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id', 'idx_order_items_product_id');
        });

        // 3. Add foreign key indexes on order_subscriptions
        Schema::table('order_subscriptions', function (Blueprint $table) {
            $table->index('user_id', 'idx_order_subscriptions_user_id');
            $table->index('vendor_listing_id', 'idx_order_subscriptions_listing_id');
        });

        // 4. Add foreign key index on support_tickets
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index('user_id', 'idx_support_tickets_user_id');
        });

        // 5. Add compound index on orders for scheduled and delivery timestamps
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['scheduled_delivery_at', 'delivered_at'], 'idx_orders_delivery_timestamps');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_delivery_timestamps');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex('idx_support_tickets_user_id');
        });

        Schema::table('order_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_order_subscriptions_listing_id');
            $table->dropIndex('idx_order_subscriptions_user_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_product_id');
        });

        Schema::table('vendor_ratings', function (Blueprint $table) {
            $table->dropUnique('idx_vendor_ratings_user_vendor_unique');
        });
    }
};
