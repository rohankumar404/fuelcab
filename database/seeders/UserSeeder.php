<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password123!');

        // ── 1. Create or update Super Admin Account ───────────────────────
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@fuelcab.com'],
            [
                'name'      => 'Super Admin',
                'password'  => $password,
                'role_type' => UserRole::SuperAdmin,
            ]
        );
        $superAdmin->syncRoles([UserRole::SuperAdmin->value]);

        $superAdminAlt = User::updateOrCreate(
            ['email' => 'superadmin@fuelcab.com'],
            [
                'name'      => 'Super Admin',
                'password'  => $password,
                'role_type' => UserRole::SuperAdmin,
            ]
        );
        $superAdminAlt->syncRoles([UserRole::SuperAdmin->value]);

        // ── 2. Create or update Operations User Account ───────────────────
        $operations = User::updateOrCreate(
            ['email' => 'operations@fuelcab.com'],
            [
                'name'      => 'Operations Manager',
                'password'  => $password,
                'role_type' => UserRole::OperationsTeam,
            ]
        );
        $operations->syncRoles([UserRole::OperationsTeam->value]);

        // ── 3. Ensure Vendor Company & Approved Vendor exist ──────────────
        $companyId = Str::uuid()->toString();
        $companyExists = DB::table('companies')->where('name', 'EcoFuel Logistics Pvt Ltd')->first();
        if ($companyExists) {
            $companyId = $companyExists->id;
        } else {
            DB::table('companies')->insert([
                'id'         => $companyId,
                'name'       => 'EcoFuel Logistics Pvt Ltd',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $vendor = Vendor::firstOrCreate(
            ['company_id' => $companyId],
            [
                'brand_name'            => 'EcoFuel Energy Corp',
                'status'                => VendorStatus::Approved,
                'commission_rate'        => 5.00,
                'is_first_party'        => false,
                'service_radius_meters' => 50000,
            ]
        );

        // ── 4. Create or update Vendor Admin Account ──────────────────────
        $vendorAdmin = User::updateOrCreate(
            ['email' => 'vendor@fuelcab.com'],
            [
                'name'      => 'Vendor Administrator',
                'password'  => $password,
                'role_type' => UserRole::VendorAdmin,
                'vendor_id' => $vendor->id,
            ]
        );
        $vendorAdmin->syncRoles([UserRole::VendorAdmin->value]);
        $vendorAdmin->update(['vendor_id' => $vendor->id]);

        // ── 5. Create or update Customer Account ──────────────────────────
        $customer = User::updateOrCreate(
            ['email' => 'customer@fuelcab.com'],
            [
                'name'      => 'Test Customer',
                'password'  => $password,
                'role_type' => UserRole::Customer,
            ]
        );
        $customer->syncRoles([UserRole::Customer->value]);
    }
}
