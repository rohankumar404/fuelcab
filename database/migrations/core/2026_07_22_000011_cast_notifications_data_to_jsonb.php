<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PostgreSQL: cast notifications.data from text → jsonb
 *
 * Filament's database-notifications widget queries:
 *   WHERE "data"->>'format' = 'filament'
 *
 * The ->> operator is only valid on json/jsonb columns, NOT on text.
 * We must convert the column type to jsonb using an explicit USING cast.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Safely cast text → jsonb. Rows with NULL data stay NULL.
            DB::statement("
                ALTER TABLE notifications
                ALTER COLUMN data TYPE jsonb
                USING CASE
                    WHEN data IS NULL THEN NULL
                    ELSE data::jsonb
                END
            ");
        }
        // SQLite/MySQL: no action needed (TEXT with JSON_EXTRACT works natively)
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("
                ALTER TABLE notifications
                ALTER COLUMN data TYPE text
                USING data::text
            ");
        }
    }
};
