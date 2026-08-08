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
            $table->string('delivery_otp', 10)->nullable();
            $table->string('delivery_proof_photo', 255)->nullable();
            $table->text('delivery_proof_signature')->nullable(); // can store path or base64 data URI
            $table->timestamp('otp_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'delivery_otp',
                'delivery_proof_photo',
                'delivery_proof_signature',
                'otp_verified_at',
            ]);
        });
    }
};
