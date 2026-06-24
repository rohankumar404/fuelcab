<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('vendor_id');
            $table->string('document_type', 100);
            $table->string('file_path', 512);
            $table->string('status', 50)->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->date('expires_at')->nullable();

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('vendor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};
