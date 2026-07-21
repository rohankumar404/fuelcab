<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make carts.user_id nullable to support guest carts.
 * Guest carts are identified by carts.guest_token.
 * When a guest logs in, their cart is merged and user_id is set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->uuid('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->uuid('user_id')->nullable(false)->change();
        });
    }
};
