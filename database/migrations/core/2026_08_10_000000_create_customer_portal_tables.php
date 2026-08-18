<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Favorites Table
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vendor_listing_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_listing_id')->references('id')->on('vendor_listings')->onDelete('cascade');
            $table->unique(['user_id', 'vendor_listing_id']);
        });

        // 2. Subscriptions Table
        Schema::create('order_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vendor_listing_id');
            $table->decimal('quantity', 12, 2);
            $table->string('frequency', 50)->default('weekly'); // daily, weekly, monthly
            $table->string('status', 50)->default('active'); // active, paused, cancelled
            $table->timestamp('next_delivery_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_listing_id')->references('id')->on('vendor_listings')->onDelete('cascade');
        });

        // 3. Support Tickets Table
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('subject', 255);
            $table->text('message');
            $table->string('status', 50)->default('open'); // open, closed
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 4. Add emergency columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_emergency')->default(false)->index();
            $table->decimal('emergency_fee', 12, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_emergency', 'emergency_fee']);
        });
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('order_subscriptions');
        Schema::dropIfExists('user_favorites');
    }
};
