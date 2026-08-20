<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Actions;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\Log;

class UpdateVendorSettingsAction
{
    /**
     * Whitelisted vendor settings fields that a vendor admin can update.
     */
    private const ALLOWED_FIELDS = [
        'business_name',
        'contact_email',
        'contact_phone',
        'address',
        'description',
        'logo',
        'website_url',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_account_name',
    ];

    /**
     * Update whitelisted vendor settings fields.
     *
     * @param  array<string, mixed>  $settings
     */
    public function execute(string $vendorId, array $settings): Vendor
    {
        $vendor = Vendor::findOrFail($vendorId);

        $filteredSettings = array_intersect_key($settings, array_flip(self::ALLOWED_FIELDS));

        $vendor->update($filteredSettings);

        Log::info('[UpdateVendorSettingsAction] Vendor settings updated.', [
            'vendor_id' => $vendorId,
            'fields' => array_keys($filteredSettings),
        ]);

        return $vendor->fresh();
    }
}
