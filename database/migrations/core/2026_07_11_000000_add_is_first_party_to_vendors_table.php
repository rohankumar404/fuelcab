<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->boolean('is_first_party')->default(false)->index();
            $table->string('business_phone', 20)->nullable();
            $table->string('gst_number', 15)->nullable();
            $table->string('pan_number', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->dropColumn(['is_first_party', 'business_phone', 'gst_number', 'pan_number']);
        });
    }
};
