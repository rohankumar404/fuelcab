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
        Schema::create('addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('addressable_type');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20);
            $table->string('country', 100)->default('India');
            // Spatial coordinates stored as high-precision decimals (fallback for non-PostGIS setups)
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->boolean('is_primary')->default(false);

            // Audit & Timestamps
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('company_id');
            // Composite index for common geo bounding-box queries (without PostGIS)
            $table->index(['latitude', 'longitude'], 'idx_addresses_lat_lng');
        });

        if (DB::getDriverName() === 'pgsql') {
            $postgisAvailable = DB::select(
                "SELECT 1 FROM pg_available_extensions WHERE name = 'postgis'"
            );

            if (!empty($postgisAvailable)) {
                DB::statement("CREATE EXTENSION IF NOT EXISTS postgis");
                DB::statement('ALTER TABLE addresses ADD COLUMN geo_point GEOGRAPHY(POINT, 4326) NULL');
                DB::statement('CREATE INDEX idx_addresses_geo_point ON addresses USING GIST (geo_point)');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
