<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            // Order identity
            $table->string('order_number', 30)->nullable()->unique()->after('id');

            // Cancellation metadata
            $table->text('cancel_reason')->nullable()->after('channel');

            // Miscellaneous
            $table->text('notes')->nullable()->after('cancel_reason');
            $table->string('payment_method', 50)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'order_number',
                'cancel_reason',
                'notes',
                'payment_method',
            ]);
        });
    }
};
