<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->foreignUuid('marketplace_product_id')
                ->constrained('marketplace_products')
                ->cascadeOnDelete();

            // Core listing identity
            $table->string('listing_title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();

            // Descriptions
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();

            // Media — JSON array of image URLs
            $table->jsonb('product_images')->default('[]');

            // Quantity & unit
            $table->decimal('min_order_quantity', 12, 4)->default(1);
            $table->decimal('max_order_quantity', 12, 4)->nullable();
            $table->string('unit');           // UnitOfMeasure enum value
            $table->decimal('available_quantity', 12, 4)->default(0);

            // Pricing & tax
            $table->decimal('base_price', 12, 4);
            $table->decimal('tax_rate', 5, 2)->default(18.00);  // GST %
            $table->boolean('tax_inclusive')->default(false);

            // Logistics
            $table->string('dispatch_location')->nullable();
            $table->jsonb('serviceable_locations')->default('[]');
            $table->unsignedInteger('estimated_dispatch_hours')->nullable();

            // Quality — flexible JSONB (GCV, Moisture, Ash, Sulphur, Density, etc.)
            $table->jsonb('quality_specifications')->nullable();

            // Certificate documents — JSON array of file URLs
            $table->jsonb('certificate_documents')->default('[]');

            // Status flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Approval lifecycle
            $table->string('approval_status')->default('DRAFT');
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for common queries
            $table->index('vendor_id');
            $table->index('marketplace_product_id');
            $table->index('approval_status');
            $table->index(['approval_status', 'is_active']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_listings');
    }
};
