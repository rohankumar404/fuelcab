<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'brand_name'          => $this->brand_name,
            'legal_name'          => $this->legal_name,
            'vendor_code'         => $this->vendor_code,
            'gst_number'          => $this->gst_number,
            'pan'                 => $this->pan,
            'company_type'        => $this->company_type,
            'contact_person'      => $this->contact_person,
            'mobile'              => $this->mobile,
            'email'               => $this->email,
            'registered_address'  => $this->registered_address,
            'operational_address' => $this->operational_address,
            'city'                => $this->city,
            'state'               => $this->state,
            'pincode'             => $this->pincode,
            'latitude'            => $this->latitude,
            'longitude'           => $this->longitude,
            'status'              => $this->status?->value,
            'status_label'        => $this->status?->label(),
            'verification_status' => $this->verification_status?->value,
            'is_first_party'      => $this->is_first_party,
            'commission_rate'     => $this->commission_rate,
            'documents'           => VendorDocumentResource::collection($this->whenLoaded('documents')),
            'created_at'          => $this->created_at?->toISOString(),
        ];
    }
}
