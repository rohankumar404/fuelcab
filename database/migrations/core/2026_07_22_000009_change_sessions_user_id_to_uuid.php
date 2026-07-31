<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL requires explicit USING cast when altering column type from bigint to varchar/uuid
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE varchar(255) USING user_id::varchar');
        } else {
            Schema::table('sessions', function (Blueprint $table): void {
                $table->string('user_id', 255)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING NULL');
        } else {
            Schema::table('sessions', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }
};
