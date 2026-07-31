<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix notifications.notifiable_id column type.
 *
 * morphs('notifiable') creates notifiable_id as unsignedBigInteger (bigint).
 * Our User model uses UUID primary keys (varchar/string).
 *
 * PostgreSQL throws:
 *   SQLSTATE[22P02]: invalid input syntax for type bigint: "f54e911b-..."
 *
 * Fix: cast notifiable_id from bigint → varchar(255) using an explicit USING clause.
 * Also drop and recreate the composite index.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Drop the composite index created by morphs() before altering the column
            DB::statement('DROP INDEX IF EXISTS notifications_notifiable_type_notifiable_id_index');

            // Cast bigint → varchar(255) so UUID strings are accepted
            DB::statement('
                ALTER TABLE notifications
                ALTER COLUMN notifiable_id TYPE varchar(255)
                USING notifiable_id::varchar
            ');

            // Recreate the composite morph index
            DB::statement('
                CREATE INDEX notifications_notifiable_type_notifiable_id_index
                ON notifications (notifiable_type, notifiable_id)
            ');
        }
        // SQLite and MySQL: no action needed — morphs() uses string on these drivers
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS notifications_notifiable_type_notifiable_id_index');

            DB::statement('
                ALTER TABLE notifications
                ALTER COLUMN notifiable_id TYPE bigint
                USING NULL
            ');

            DB::statement('
                CREATE INDEX notifications_notifiable_type_notifiable_id_index
                ON notifications (notifiable_type, notifiable_id)
            ');
        }
    }
};
