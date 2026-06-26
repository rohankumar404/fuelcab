<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('status', 50)->default('draft'); // draft, published, archived
            $table->uuid('author_id')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('deleted_at');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
