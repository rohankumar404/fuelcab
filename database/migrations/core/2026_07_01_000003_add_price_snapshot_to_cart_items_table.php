<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            // Price locked at time of add — so stale items can be detected
            $table->decimal('price_snapshot', 12, 4)->default(0)->after('quantity');
            $table->string('unit_of_measure', 50)->default('liter')->after('price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropColumn(['price_snapshot', 'unit_of_measure']);
        });
    }
};
