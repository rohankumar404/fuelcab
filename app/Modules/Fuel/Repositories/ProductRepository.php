<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Repositories;

use App\Modules\Fuel\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Get paginated products with optional filters.
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['category', 'inventory'])
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['vendor_id']), fn ($q) => $q->where('vendor_id', $filters['vendor_id']))
            ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['search']), fn ($q) => $q->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%");
            }))
            ->orderBy('display_order', 'asc')
            ->latest();

        return $query->paginate($perPage);
    }

    /**
     * Get all active/orderable products.
     */
    public function getActive(): Collection
    {
        return Product::with(['category', 'inventory'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Find a single product by ID with relations.
     */
    public function findById(string $id): Product
    {
        return Product::with(['category', 'vendor', 'inventory'])
            ->findOrFail($id);
    }

    /**
     * Find product by SKU.
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }
}
