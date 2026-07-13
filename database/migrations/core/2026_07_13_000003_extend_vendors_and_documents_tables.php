<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            if (!Schema::hasColumn('vendors', 'legal_name')) {
                $table->string('legal_name', 255)->nullable()->after('brand_name');
            }
            if (!Schema::hasColumn('vendors', 'vendor_code')) {
                $table->string('vendor_code', 50)->nullable()->unique()->after('legal_name');
            }
            if (!Schema::hasColumn('vendors', 'gst_number')) {
                $table->string('gst_number', 50)->nullable()->after('vendor_code');
            }
            if (!Schema::hasColumn('vendors', 'pan')) {
                $table->string('pan', 50)->nullable()->after('gst_number');
            }
            if (!Schema::hasColumn('vendors', 'company_type')) {
                $table->string('company_type', 100)->nullable()->after('pan');
            }
            if (!Schema::hasColumn('vendors', 'contact_person')) {
                $table->string('contact_person', 255)->nullable()->after('company_type');
            }
            if (!Schema::hasColumn('vendors', 'mobile')) {
                $table->string('mobile', 50)->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('vendors', 'email')) {
                $table->string('email', 150)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('vendors', 'registered_address')) {
                $table->text('registered_address')->nullable()->after('email');
            }
            if (!Schema::hasColumn('vendors', 'operational_address')) {
                $table->text('operational_address')->nullable()->after('registered_address');
            }
            if (!Schema::hasColumn('vendors', 'city')) {
                $table->string('city', 100)->nullable()->after('operational_address');
            }
            if (!Schema::hasColumn('vendors', 'state')) {
                $table->string('state', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('vendors', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('vendors', 'latitude')) {
                $table->decimal('latitude', 9, 6)->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('vendors', 'longitude')) {
                $table->decimal('longitude', 9, 6)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('vendors', 'verification_status')) {
                $table->string('verification_status', 50)->default('pending')->after('status')->index();
            }
            if (!Schema::hasColumn('vendors', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('verification_status');
            }
        });

        Schema::table('vendor_documents', function (Blueprint $table): void {
            if (!Schema::hasColumn('vendor_documents', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('vendor_documents', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->dropColumn([
                'legal_name',
                'vendor_code',
                'gst_number',
                'pan',
                'company_type',
                'contact_person',
                'mobile',
                'email',
                'registered_address',
                'operational_address',
                'city',
                'state',
                'pincode',
                'latitude',
                'longitude',
                'verification_status',
                'internal_notes'
            ]);
        });

        Schema::table('vendor_documents', function (Blueprint $table): void {
            $table->dropColumn(['rejection_reason', 'internal_notes']);
        });
    }
};
