<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('notification_id')->nullable(); // ref to notifications table
            $table->uuid('user_id');
            $table->string('channel', 50); // push, sms, email
            $table->string('status', 50)->default('sent'); // queued, sent, failed
            $table->text('error_message')->nullable();
            $table->string('provider', 100)->nullable(); // Firebase, Twilio, SES
            $table->json('provider_response')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
