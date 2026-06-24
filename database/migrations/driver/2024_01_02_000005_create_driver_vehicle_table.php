<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_vehicle', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('driver_id');
            $table->uuid('vehicle_id');
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            // Unique active assignment
            $table->unique(['driver_id', 'vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_vehicle');
    }
};
