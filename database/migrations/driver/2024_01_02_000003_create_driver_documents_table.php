<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('driver_id');
            $table->string('document_type', 100); // license, rc, insurance, police_clearance
            $table->string('file_path', 512);
            $table->string('status', 50)->default('pending'); // pending, verified, rejected
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->date('expires_at')->nullable();

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['driver_id', 'document_type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};
