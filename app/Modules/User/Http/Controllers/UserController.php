<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserFavorite;
use App\Modules\Order\Models\Order;
use App\Modules\Wallet\Models\Wallet;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ApiResponse;

    // ── Profile ──────────────────────────────────────────────────────────────

    public function profile(Request $request): JsonResponse
    {
        return $this->success($request->user(), 'Profile retrieved successfully.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'   => 'sometimes|required|string|max:255',
            'email'  => 'sometimes|required|email|max:255|unique:users,email,'.$user->id,
            'phone'  => 'sometimes|nullable|string|max:20|unique:users,mobile,'.$user->id,
            'mobile' => 'sometimes|nullable|string|max:20|unique:users,mobile,'.$user->id,
        ]);

        // Allow 'phone' as alias for 'mobile'
        if (isset($validated['phone']) && !isset($validated['mobile'])) {
            $validated['mobile'] = $validated['phone'];
        }
        unset($validated['phone']);

        $user->update($validated);
        $user->refresh();

        return $this->success($user, 'Profile updated successfully.');
    }

    // ── Addresses ────────────────────────────────────────────────────────────

    public function listAddresses(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();

        return $this->success($addresses, 'Addresses retrieved successfully.');
    }

    public function createAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'state'          => 'nullable|string|max:100',
            'postal_code'    => 'required|string|max:20',
            'country'        => 'nullable|string|max:100',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
        ]);

        $address = Address::create(array_merge($validated, [
            'addressable_type' => User::class,
            'user_id'          => $request->user()->id,
            'country'          => $validated['country'] ?? 'India',
        ]));

        return $this->success($address, 'Address created successfully.', 201);
    }

    public function updateAddress(Request $request, string $id): JsonResponse
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'address_line_1' => 'sometimes|required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
        ]);

        $address->update($validated);

        return $this->success($address, 'Address updated successfully.');
    }

    public function deleteAddress(Request $request, string $id): JsonResponse
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);

        $address->delete();

        return $this->success(null, 'Address deleted successfully.');
    }

    // ── Favorites ────────────────────────────────────────────────────────────

    public function listFavorites(Request $request): JsonResponse
    {
        $favorites = UserFavorite::with('listing.marketplaceProduct')
            ->where('user_id', $request->user()->id)
            ->get();

        return $this->success($favorites, 'Favorites retrieved successfully.');
    }

    public function addFavorite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_listing_id' => 'required|uuid|exists:vendor_listings,id',
        ]);

        $favorite = UserFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'vendor_listing_id' => $validated['vendor_listing_id'],
        ]);

        return $this->success($favorite, 'Listing added to favorites.', 201);
    }

    public function deleteFavorite(Request $request, string $listingId): JsonResponse
    {
        $favorite = UserFavorite::where('user_id', $request->user()->id)
            ->where('vendor_listing_id', $listingId)
            ->firstOrFail();

        $favorite->delete();

        return $this->success(null, 'Listing removed from favorites.');
    }

    // ── Dashboard Summary ────────────────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalOrders = Order::where('customer_id', $user->id)->count();
        $totalSpend = Order::where('customer_id', $user->id)->sum('total_amount');
        $walletBalance = Wallet::where('user_id', $user->id)->first()?->balance ?? 0.00;

        $activeSubscriptions = DB::table('order_subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $unreadNotifications = $user->unreadNotifications()->count();

        return $this->success([
            'total_orders' => $totalOrders,
            'total_spend' => (float) $totalSpend,
            'wallet_balance' => (float) $walletBalance,
            'active_subscriptions' => $activeSubscriptions,
            'unread_notifications' => $unreadNotifications,
        ], 'Dashboard summary retrieved successfully.');
    }

    // ── Support Tickets ──────────────────────────────────────────────────────

    public function listTickets(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($tickets, 'Support tickets retrieved successfully.');
    }

    public function createTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'status' => 'open',
        ]));

        return $this->success($ticket, 'Support ticket submitted successfully.', 201);
    }
}
