<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Http\Resources\VendorResource;
use App\Modules\Vendor\Http\Requests\UpdateVendorProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * GET /api/v1/vendor/profile
     *
     * Returns the authenticated vendor user's own vendor profile.
     * Vendor users cannot list or access other vendors — only their own.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'No vendor profile associated with this account.',
            ], 404);
        }

        $vendor = Vendor::with(['documents'])->findOrFail($user->vendor_id);

        $this->authorize('view', $vendor);

        return response()->json([
            'success' => true,
            'data'    => new VendorResource($vendor),
        ]);
    }

    /**
     * PUT /api/v1/vendor/profile
     *
     * Vendor admin can update their own profile fields (not status).
     */
    public function updateProfile(UpdateVendorProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'No vendor profile associated with this account.',
            ], 404);
        }

        $vendor = Vendor::findOrFail($user->vendor_id);
        $this->authorize('update', $vendor);

        $vendor->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Vendor profile updated successfully.',
            'data'    => new VendorResource($vendor->fresh()),
        ]);
    }

    // ── Super Admin Actions ──────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/vendors
     * Super Admin / Operations: list all vendors.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vendor::class);

        $vendors = Vendor::with('documents')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => VendorResource::collection($vendors),
            'meta'    => [
                'current_page' => $vendors->currentPage(),
                'last_page'    => $vendors->lastPage(),
                'total'        => $vendors->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/vendors/{vendor}
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $this->authorize('view', $vendor);

        return response()->json([
            'success' => true,
            'data'    => new VendorResource($vendor->load('documents')),
        ]);
    }

    /**
     * POST /api/v1/admin/vendors/{vendor}/approve
     */
    public function approve(Vendor $vendor): JsonResponse
    {
        $this->authorize('approve', $vendor);

        $vendor->update([
            'status'              => VendorStatus::Approved,
            'verification_status' => \App\Modules\Vendor\Enums\DocumentStatus::Verified,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Vendor '{$vendor->brand_name}' has been approved.",
            'data'    => new VendorResource($vendor->fresh()),
        ]);
    }

    /**
     * POST /api/v1/admin/vendors/{vendor}/reject
     */
    public function reject(Request $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('reject', $vendor);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $vendor->update([
            'status'         => VendorStatus::Rejected,
            'internal_notes' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Vendor '{$vendor->brand_name}' has been rejected.",
            'data'    => new VendorResource($vendor->fresh()),
        ]);
    }

    /**
     * POST /api/v1/admin/vendors/{vendor}/suspend
     */
    public function suspend(Request $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('suspend', $vendor);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $vendor->update([
            'status'         => VendorStatus::Suspended,
            'internal_notes' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Vendor '{$vendor->brand_name}' has been suspended.",
            'data'    => new VendorResource($vendor->fresh()),
        ]);
    }

    /**
     * POST /api/v1/admin/vendors/{vendor}/reactivate
     */
    public function reactivate(Vendor $vendor): JsonResponse
    {
        $this->authorize('reactivate', $vendor);

        $vendor->update(['status' => VendorStatus::Approved]);

        return response()->json([
            'success' => true,
            'message' => "Vendor '{$vendor->brand_name}' has been reactivated.",
            'data'    => new VendorResource($vendor->fresh()),
        ]);
    }

    /**
     * POST /api/v1/admin/vendors/{vendor}/notes
     */
    public function addNotes(Request $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('addNotes', $vendor);

        $request->validate([
            'notes' => 'required|string|max:5000',
        ]);

        $vendor->update(['internal_notes' => $request->notes]);

        return response()->json([
            'success' => true,
            'message' => 'Internal notes saved.',
        ]);
    }
}
