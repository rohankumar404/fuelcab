<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            if (!Schema::hasColumn('categories', 'image_path')) {
                $table->string('image_path')->nullable();
            }
            if (!Schema::hasColumn('categories', 'display_order')) {
                $table->integer('display_order')->default(0)->index();
            }
            if (!Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
            if (!Schema::hasColumn('categories', 'seo_title')) {
                $table->string('seo_title')->nullable();
            }
            if (!Schema::hasColumn('categories', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn([
                'image_path',
                'display_order',
                'is_active',
                'seo_title',
                'seo_description'
            ]);
        });
    }
};
