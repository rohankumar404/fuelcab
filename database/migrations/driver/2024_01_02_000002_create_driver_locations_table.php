<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_locations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('driver_id')->unique(); // one active location per driver
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->decimal('heading', 5, 2)->nullable(); // compass bearing in degrees
            $table->decimal('speed_kmh', 6, 2)->nullable();
            $table->timestamp('recorded_at');

            // Timestamps only (no soft deletes — location is overwritten)
            $table->timestamps();

            // Foreign Keys
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');

            // Indexes
            $table->index(['latitude', 'longitude'], 'idx_driver_location_lat_lng');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};
