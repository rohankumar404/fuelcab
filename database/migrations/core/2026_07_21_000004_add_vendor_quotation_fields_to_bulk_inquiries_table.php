<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulk_inquiries', function (Blueprint $table): void {
            if (! Schema::hasColumn('bulk_inquiries', 'vendor_id')) {
                $table->uuid('vendor_id')->nullable()->after('product_id');
                $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
                $table->index('vendor_id');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'vendor_listing_id')) {
                $table->uuid('vendor_listing_id')->nullable()->after('vendor_id');
                $table->foreign('vendor_listing_id')->references('id')->on('vendor_listings')->onDelete('cascade');
                $table->index('vendor_listing_id');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'quoted_price')) {
                $table->decimal('quoted_price', 12, 2)->nullable()->after('status');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'quoted_unit')) {
                $table->string('quoted_unit', 50)->nullable()->after('quoted_price');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'quoted_min_quantity')) {
                $table->decimal('quoted_min_quantity', 12, 2)->nullable()->after('quoted_unit');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'validity_date')) {
                $table->date('validity_date')->nullable()->after('quoted_min_quantity');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'dispatch_time')) {
                $table->string('dispatch_time', 100)->nullable()->after('validity_date');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'terms')) {
                $table->text('terms')->nullable()->after('dispatch_time');
            }

            if (! Schema::hasColumn('bulk_inquiries', 'notes')) {
                $table->text('notes')->nullable()->after('terms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulk_inquiries', function (Blueprint $table): void {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['vendor_listing_id']);
            $table->dropColumn([
                'vendor_id',
                'vendor_listing_id',
                'quoted_price',
                'quoted_unit',
                'quoted_min_quantity',
                'validity_date',
                'dispatch_time',
                'terms',
                'notes',
            ]);
        });
    }
};
