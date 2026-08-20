<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Vendor\Models\Vendor;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorRating extends Model
{
    use HasUuid;

    protected $table = 'vendor_ratings';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'rating',
        'review',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
