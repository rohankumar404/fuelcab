<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorDocumentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'vendor_id'        => $this->vendor_id,
            'document_type'    => $this->document_type,
            'status'           => $this->status?->value,
            'status_label'     => $this->status?->label(),
            'verified_at'      => $this->verified_at?->toISOString(),
            'expires_at'       => $this->expires_at?->toDateString(),
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at?->toISOString(),
            // Intentionally omit file_path from API response for security
        ];
    }
}
