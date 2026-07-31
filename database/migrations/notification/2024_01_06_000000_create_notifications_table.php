<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standard Laravel polymorphic notifications table — UUID-aware.
 *
 * Uses uuidMorphs() so notifiable_id is stored as varchar (not bigint),
 * which is required when primary keys on notifiable models are UUIDs.
 *
 * data column is jsonb (not text) for PostgreSQL ->> JSON operator support.
 *
 * Compatible with:
 *  - Laravel's Notifiable trait  (User::notify())
 *  - Filament's DatabaseNotifications widget
 *  - Any polymorphic ->notify() call on UUID-keyed models
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');             // Notification class FQCN
            $table->uuidMorphs('notifiable');   // notifiable_type (string) + notifiable_id (uuid/varchar) + index
            $table->jsonb('data');              // jsonb required for PostgreSQL ->> operator
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
