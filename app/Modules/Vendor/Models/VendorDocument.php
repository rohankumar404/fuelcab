<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Models;

use App\Models\User;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorDocument extends Model
{
    use HasUuid, Auditable, SoftDeletes;

    protected $table = 'vendor_documents';

    protected $fillable = [
        'vendor_id',
        'document_type',
        'file_path',
        'status',
        'verified_at',
        'verified_by',
        'expires_at',
        'rejection_reason',
        'internal_notes',
    ];

    protected $casts = [
        'status'      => DocumentStatus::class,
        'verified_at' => 'datetime',
        'expires_at'  => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
