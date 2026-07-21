<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table): void {
            if (! Schema::hasColumn('settlements', 'adjustments')) {
                $table->decimal('adjustments', 12, 2)->default(0.00)->after('commission_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table): void {
            if (Schema::hasColumn('settlements', 'adjustments')) {
                $table->dropColumn('adjustments');
            }
        });
    }
};
