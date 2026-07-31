<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recreate the notifications table to match the standard Laravel polymorphic
 * notifications schema required by Filament's DatabaseNotifications widget.
 *
 * The original custom schema used (user_id, type, title, body) which is
 * incompatible with:
 *   - Filament's filament.livewire.database-notifications component
 *   - Laravel's Notifiable trait
 *   - Any ->notify() call on User models
 *
 * Standard schema needs: notifiable_type, notifiable_id, data (json), read_at
 */
return new class extends Migration
{
    public function up(): void
    {
        // Back up any existing notification rows to a temp table (if any exist)
        Schema::dropIfExists('notifications_legacy_backup');
        DB::statement('CREATE TABLE notifications_legacy_backup AS SELECT * FROM notifications');

        // Drop the old custom table
        Schema::dropIfExists('notifications');

        // Recreate with the correct Laravel/Filament polymorphic schema
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // Polymorphic morph columns — required by Laravel Notifiable + Filament
            $table->string('type');                   // Notification class FQCN
            $table->morphs('notifiable');             // notifiable_type + notifiable_id (string index)
            $table->text('data');                     // JSON payload
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Rename the morphs index so it works with UUID IDs in PostgreSQL
        // morphs() creates notifiable_type + notifiable_id as string by default ✓
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');

        // Restore from backup if it exists
        if (Schema::hasTable('notifications_legacy_backup')) {
            DB::statement('CREATE TABLE notifications AS SELECT * FROM notifications_legacy_backup');
            Schema::dropIfExists('notifications_legacy_backup');
        }
    }
};
