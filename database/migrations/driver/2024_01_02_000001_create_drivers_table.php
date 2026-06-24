<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->uuid('vendor_id')->nullable();
            $table->string('license_number', 100)->unique();
            $table->date('license_expiry');
            $table->string('status', 50)->default('offline'); // offline, available, on_trip, suspended
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->uuid('approved_by')->nullable();

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
