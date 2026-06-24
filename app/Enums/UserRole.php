<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin     = 'super_admin';
    case OperationsTeam = 'operations_team';
    case VendorAdmin    = 'vendor_admin';
    case VendorStaff    = 'vendor_staff';
    case Driver         = 'driver';
    case Customer       = 'customer';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin     => 'Super Admin',
            self::OperationsTeam => 'Operations Team',
            self::VendorAdmin    => 'Vendor Admin',
            self::VendorStaff    => 'Vendor Staff',
            self::Driver         => 'Driver',
            self::Customer       => 'Customer',
        };
    }

    public function sanctumAbilities(): array
    {
        return match($this) {
            self::SuperAdmin     => ['*'],
            self::OperationsTeam => ['operations:*'],
            self::VendorAdmin    => ['vendor:admin:*'],
            self::VendorStaff    => ['vendor:staff:*'],
            self::Driver         => ['driver:*'],
            self::Customer       => ['customer:*'],
        };
    }
}
