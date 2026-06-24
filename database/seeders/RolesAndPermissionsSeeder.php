<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\UserRole;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Guard name (using Sanctum or web API standard)
        $guardName = 'api';

        // 2. Core Permission Matrix Groups
        $permissions = [
            // Core Platform & Global Administration
            'platform' => [
                'manage_system_settings',
                'view_audit_logs',
                'manage_integrations',
            ],
            // Vendor management
            'vendors' => [
                'create_vendors',
                'view_vendors',
                'update_vendors',
                'delete_vendors',
                'approve_vendors',
                'manage_vendor_settings',
            ],
            // User & Access management
            'users' => [
                'create_users',
                'view_users',
                'update_users',
                'delete_users',
                'assign_roles',
            ],
            // Driver management
            'drivers' => [
                'create_drivers',
                'view_drivers',
                'update_drivers',
                'delete_drivers',
                'approve_drivers',
                'track_drivers',
            ],
            // Vehicle management
            'vehicles' => [
                'create_vehicles',
                'view_vehicles',
                'update_vehicles',
                'delete_vehicles',
            ],
            // Order management
            'orders' => [
                'create_orders',
                'view_orders',
                'update_orders',
                'cancel_orders',
                'dispatch_orders',
                'assign_drivers',
            ],
            // Fuel inventory / pricing management
            'fuel' => [
                'create_fuel_types',
                'view_fuel_types',
                'update_fuel_types',
                'delete_fuel_types',
                'manage_fuel_pricing',
                'update_fuel_inventory',
            ],
            // Payment / Wallet management
            'payments' => [
                'initiate_payments',
                'view_payments',
                'process_refunds',
                'manage_vendor_settlements',
                'top_up_wallets',
                'view_wallets',
            ],
            // Reporting & Analytics
            'reports' => [
                'view_system_analytics',
                'view_vendor_analytics',
                'export_reports',
            ],
        ];

        // 3. Create all permissions in DB
        $dbPermissions = [];
        foreach ($permissions as $module => $modulePerms) {
            foreach ($modulePerms as $perm) {
                $dbPermissions[$perm] = Permission::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => $guardName
                ]);
            }
        }

        // 4. Create roles and sync permissions based on role definitions
        
        // Super Admin
        $superAdminRole = Role::firstOrCreate(['name' => UserRole::SuperAdmin->value, 'guard_name' => $guardName]);
        // Super admin inherits all permissions implicitly via Gate::before in AuthServiceProvider or AppServiceProvider,
        // but we can sync all for explicit clarity in testing.
        $superAdminRole->syncPermissions(Permission::where('guard_name', $guardName)->get());

        // Operations Team
        $operationsRole = Role::firstOrCreate(['name' => UserRole::OperationsTeam->value, 'guard_name' => $guardName]);
        $operationsRole->syncPermissions([
            'view_audit_logs',
            'view_vendors',
            'update_vendors',
            'approve_vendors',
            'view_users',
            'view_drivers',
            'update_drivers',
            'approve_drivers',
            'track_drivers',
            'create_vehicles',
            'view_vehicles',
            'update_vehicles',
            'view_orders',
            'update_orders',
            'cancel_orders',
            'dispatch_orders',
            'assign_drivers',
            'view_fuel_types',
            'manage_fuel_pricing',
            'update_fuel_inventory',
            'view_payments',
            'process_refunds',
            'manage_vendor_settlements',
            'view_wallets',
            'view_system_analytics',
            'view_vendor_analytics',
            'export_reports',
        ]);

        // Vendor Admin (Tenant boundary logic managed in VendorScope / custom logic)
        $vendorAdminRole = Role::firstOrCreate(['name' => UserRole::VendorAdmin->value, 'guard_name' => $guardName]);
        $vendorAdminRole->syncPermissions([
            'view_vendors',
            'manage_vendor_settings',
            'view_users',
            'create_users', // to create vendor staff
            'update_users',
            'view_drivers',
            'track_drivers',
            'create_vehicles',
            'view_vehicles',
            'update_vehicles',
            'delete_vehicles',
            'create_orders',
            'view_orders',
            'update_orders',
            'cancel_orders',
            'dispatch_orders',
            'assign_drivers',
            'view_fuel_types',
            'update_fuel_inventory',
            'view_payments',
            'view_wallets',
            'view_vendor_analytics',
            'export_reports',
        ]);

        // Vendor Staff
        $vendorStaffRole = Role::firstOrCreate(['name' => UserRole::VendorStaff->value, 'guard_name' => $guardName]);
        $vendorStaffRole->syncPermissions([
            'view_vendors',
            'view_vehicles',
            'create_orders',
            'view_orders',
            'update_orders',
            'dispatch_orders',
            'view_fuel_types',
            'update_fuel_inventory',
            'view_payments',
            'view_vendor_analytics',
        ]);

        // Driver
        $driverRole = Role::firstOrCreate(['name' => UserRole::Driver->value, 'guard_name' => $guardName]);
        $driverRole->syncPermissions([
            'view_drivers', // views own status
            'view_vehicles', // views assigned vehicle
            'view_orders', // views assigned order
            'update_orders', // update status: en_route, arrived, completed
            'view_wallets', // view driver earnings wallet
        ]);

        // Customer
        $customerRole = Role::firstOrCreate(['name' => UserRole::Customer->value, 'guard_name' => $guardName]);
        $customerRole->syncPermissions([
            'create_orders',
            'view_orders',
            'cancel_orders',
            'initiate_payments',
            'view_payments',
            'top_up_wallets',
            'view_wallets',
        ]);
    }
}
