<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Actions;

use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateVendorAction
{
    /**
     * Create a new Vendor profile.
     *
     * @param  array{
     *   company_id: string,
     *   brand_name: string,
     *   commission_rate?: float,
     *   service_radius_meters?: int,
     *   is_first_party?: bool,
     *   business_phone?: string|null,
     *   gst_number?: string|null,
     *   pan_number?: string|null,
     *   legal_name?: string|null,
     *   email?: string|null,
     *   mobile?: string|null,
     *   created_by?: string|null,
     * } $data
     */
    public function execute(array $data): Vendor
    {
        return DB::transaction(function () use ($data): Vendor {
            $vendor = Vendor::create([
                'company_id' => $data['company_id'],
                'brand_name' => $data['brand_name'],
                'status' => VendorStatus::Pending->value,
                'commission_rate' => $data['commission_rate'] ?? 10.00,
                'service_radius_meters' => $data['service_radius_meters'] ?? 5000,
                'is_first_party' => $data['is_first_party'] ?? false,
                'business_phone' => $data['business_phone'] ?? null,
                'gst_number' => $data['gst_number'] ?? null,
                'pan_number' => $data['pan_number'] ?? null,
                'legal_name' => $data['legal_name'] ?? $data['brand_name'],
                'vendor_code' => 'VEND-'.strtoupper(Str::random(8)),
                'email' => $data['email'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            Log::info('[CreateVendorAction] Vendor profile created.', [
                'vendor_id' => $vendor->id,
                'brand_name' => $vendor->brand_name,
                'vendor_code' => $vendor->vendor_code,
            ]);

            return $vendor;
        });
    }
}
