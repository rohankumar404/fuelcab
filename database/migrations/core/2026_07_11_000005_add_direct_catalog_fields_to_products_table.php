<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('product_image')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('min_order_quantity', 12, 4)->nullable();
            $table->decimal('max_order_quantity', 12, 4)->nullable();
            $table->boolean('ordering_enabled')->default(true)->index();
            $table->boolean('is_coming_soon')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->integer('display_order')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'short_description',
                'full_description',
                'product_image',
                'icon',
                'min_order_quantity',
                'max_order_quantity',
                'ordering_enabled',
                'is_coming_soon',
                'is_featured',
                'seo_title',
                'seo_description',
                'display_order'
            ]);
        });
    }
};
