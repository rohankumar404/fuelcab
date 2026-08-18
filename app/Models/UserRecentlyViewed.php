<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use App\Modules\Vendor\Models\VendorListing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRecentlyViewed extends Model
{
    use HasUuid;

    protected $table = 'user_recently_viewed';

    protected $fillable = [
        'user_id',
        'vendor_listing_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(VendorListing::class, 'vendor_listing_id');
    }
}
