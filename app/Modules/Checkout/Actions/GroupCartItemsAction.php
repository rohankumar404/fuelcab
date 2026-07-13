<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Modules\Cart\Models\Cart;
use App\Modules\Checkout\DTOs\FulfillmentGroupDTO;
use Illuminate\Support\Collection;

/**
 * Groups cart items by (sales_channel + vendor_id) into distinct
 * fulfillment groups — each group maps to exactly one Order.
 *
 * Algorithm:
 *   1. Iterate all non-deleted cart items.
 *   2. Compose a composite key: "{channel}|{vendor_id}".
 *   3. Bucket items by key.
 *   4. Wrap each bucket in a FulfillmentGroupDTO.
 *
 * Example groups for a mixed cart:
 *   direct|uuid-fuelcab   → FuelCab Direct Diesel
 *   marketplace|uuid-A    → Vendor A Biomass Briquettes
 *   marketplace|uuid-B    → Vendor B RDF
 */
class GroupCartItemsAction
{
    /**
     * @return Collection<int, FulfillmentGroupDTO>
     */
    public function execute(Cart $cart): Collection
    {
        $rawGroups = $cart->groupByFulfillment();

        return collect($rawGroups)
            ->map(fn (array $group) => FulfillmentGroupDTO::fromGroup($group));
    }
}
