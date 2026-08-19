<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('discount_type'); // 'fixed', 'percentage'
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_cart_amount', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 2. Add applied_coupon_code to carts table
        Schema::table('carts', function (Blueprint $table) {
            $table->string('applied_coupon_code')->nullable()->after('guest_token');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('applied_coupon_code');
        });

        Schema::dropIfExists('coupons');
    }
};
