<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Recently Viewed Table
        Schema::create('user_recently_viewed', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vendor_listing_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_listing_id')->references('id')->on('vendor_listings')->onDelete('cascade');
            $table->unique(['user_id', 'vendor_listing_id']);
        });

        // 2. Vendor Ratings Table
        Schema::create('vendor_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vendor_id');
            $table->integer('rating'); // 1 to 5 stars
            $table->text('review')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_ratings');
        Schema::dropIfExists('user_recently_viewed');
    }
};
