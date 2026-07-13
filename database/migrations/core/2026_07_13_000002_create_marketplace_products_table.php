<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('product_image')->nullable();
            $table->string('unit_of_measure', 50)->default('litres');
            
            // PostgreSQL JSONB for flexible specifications schema definitions
            $table->jsonb('specifications_schema')->nullable();
            
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_coming_soon')->default(false)->index();
            $table->boolean('ordering_enabled')->default(true)->index();
            
            $table->integer('display_order')->default(0)->index();
            
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            
            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict');

            // Unique name per category context
            $table->unique(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_products');
    }
};
