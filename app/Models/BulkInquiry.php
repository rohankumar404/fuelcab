<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Fuel\Models\Product;

class BulkInquiry extends Model
{
    use HasUuid;

    protected $table = 'bulk_inquiries';

    protected $fillable = [
        'user_id',
        'product_id',
        'vendor_id',
        'vendor_listing_id',
        'quantity',
        'preferred_delivery_date',
        'status', // pending, responded, closed
        'message',
        // Quotation fields (filled by vendor)
        'quoted_price',
        'quoted_unit',
        'quoted_min_quantity',
        'validity_date',
        'dispatch_time',
        'terms',
        'notes',
    ];

    protected $casts = [
        'preferred_delivery_date' => 'date',
        'validity_date'           => 'date',
        'quantity'                => 'decimal:2',
        'quoted_price'            => 'decimal:2',
        'quoted_min_quantity'     => 'decimal:2',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function listing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VendorListing::class, 'vendor_listing_id');
    }

    public function hasQuotation(): bool
    {
        return ! is_null($this->quoted_price);
    }
}
