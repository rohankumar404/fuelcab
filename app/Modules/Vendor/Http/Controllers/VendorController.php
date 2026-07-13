<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Vendor\Http\Resources\VendorResource;
use App\Modules\Vendor\Http\Requests\UpdateVendorProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    /**
     * POST /api/v1/vendor-applications
     *
     * Submits a vendor application for the authenticated user (Step 1 to Step 7).
     */
    public function submitApplication(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Step 1: Business Details
            'brand_name'            => 'required|string|max:255',
            'company_type'          => 'required|string|max:100',
            // Step 2: Contact Details
            'contact_person'        => 'required|string|max:255',
            'mobile'                => 'required|string|max:50',
            'email'                 => 'required|email|max:150',
            // Step 3: Address
            'registered_address'    => 'required|string',
            'operational_address'   => 'required|string',
            'city'                  => 'required|string|max:100',
            'state'                 => 'required|string|max:100',
            'pincode'               => 'required|string|max:20',
            'latitude'              => 'nullable|numeric|between:-90,90',
            'longitude'             => 'nullable|numeric|between:-180,180',
            // Step 4: GST and PAN
            'gst_number'            => 'required|string|max:50',
            'pan'                   => 'required|string|max:50',
            // Step 5: Document Upload (passed as files)
            'gst_certificate'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pan_card'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'business_registration' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cancelled_cheque'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'fuel_license'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pollution_compliance'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            // Step 6: Product Interests
            'product_ids'           => 'required|array',
            'product_ids.*'         => 'uuid|exists:marketplace_products,id',
        ]);

        $user = $request->user();

        if ($user->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already associated with a vendor profile.',
            ], 422);
        }

        return DB::transaction(function () use ($validated, $user, $request) {
            // Create Company
            $companyId = Str::uuid()->toString();
            DB::table('companies')->insert([
                'id'         => $companyId,
                'name'       => $validated['brand_name'] . ' Company',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Vendor
            $vendor = Vendor::create([
                'company_id'            => $companyId,
                'brand_name'            => $validated['brand_name'],
                'legal_name'            => $validated['brand_name'] . ' Pvt Ltd',
                'vendor_code'           => 'VND-' . strtoupper(Str::random(8)),
                'gst_number'            => $validated['gst_number'],
                'pan'                   => $validated['pan'],
                'company_type'          => $validated['company_type'],
                'contact_person'        => $validated['contact_person'],
                'mobile'                => $validated['mobile'],
                'email'                 => $validated['email'],
                'registered_address'    => $validated['registered_address'],
                'operational_address'   => $validated['operational_address'],
                'city'                  => $validated['city'],
                'state'                 => $validated['state'],
                'pincode'               => $validated['pincode'],
                'latitude'              => $validated['latitude'] ?? null,
                'longitude'             => $validated['longitude'] ?? null,
                'status'                => VendorStatus::Pending,
                'verification_status'   => DocumentStatus::Pending,
            ]);

            // Link user to Vendor
            $user->update(['vendor_id' => $vendor->id]);

            // Sync Product Interests
            $vendor->marketplaceProducts()->sync($validated['product_ids']);

            // Upload and create Document records
            $docTypes = ['gst_certificate', 'pan_card', 'business_registration', 'cancelled_cheque', 'fuel_license', 'pollution_compliance'];
            foreach ($docTypes as $docType) {
                if ($request->hasFile($docType)) {
                    $path = $request->file($docType)->store("vendor-documents/{$vendor->id}", 'local');
                    VendorDocument::create([
                        'vendor_id'     => $vendor->id,
                        'document_type' => $docType,
                        'file_path'     => $path,
                        'status'        => DocumentStatus::Pending,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Vendor Application Submitted successfully.',
                'data' => [
                    'vendor_id' => $vendor->id,
                    'status'    => $vendor->status->value,
                ],
            ], 201);
        });
    }

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

        return DB::transaction(function () use ($vendor) {
            $vendor->update([
                'status'              => VendorStatus::Approved,
                'verification_status' => DocumentStatus::Verified,
            ]);

            // Assign VendorAdmin role/permissions to all associated users
            $users = User::where('vendor_id', $vendor->id)->get();
            foreach ($users as $user) {
                $user->update(['role_type' => UserRole::VendorAdmin]);
                if (method_exists($user, 'syncRoles')) {
                    $user->syncRoles([UserRole::VendorAdmin->value]);
                }
            }

            // Notify vendor via event
            event(new \App\Modules\Vendor\Events\VendorApproved());

            return response()->json([
                'success' => true,
                'message' => "Vendor '{$vendor->brand_name}' has been approved.",
                'data'    => new VendorResource($vendor->fresh()),
            ]);
        });
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
