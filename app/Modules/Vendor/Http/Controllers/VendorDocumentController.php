<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Vendor\Http\Resources\VendorDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorDocumentController extends Controller
{
    /**
     * GET /api/v1/vendor/documents
     *
     * Vendor users see only their own organization's documents.
     * Super Admin can list all by vendor via GET /api/v1/admin/vendors/{vendor}/documents
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('viewAny', VendorDocument::class);

        // Vendor users are always scoped to their own vendor — IDOR prevention
        if ($user->hasAnyRole(['vendor_admin', 'vendor_staff'])) {
            if (! $user->vendor_id) {
                return response()->json(['success' => false, 'message' => 'No vendor assigned.'], 403);
            }
            $documents = VendorDocument::where('vendor_id', $user->vendor_id)->latest()->get();
        } else {
            // Admin: filter by vendor_id query param
            $query = VendorDocument::query();
            if ($request->vendor_id) {
                $query->where('vendor_id', $request->vendor_id);
            }
            $documents = $query->latest()->get();
        }

        return response()->json([
            'success' => true,
            'data'    => VendorDocumentResource::collection($documents),
        ]);
    }

    /**
     * POST /api/v1/vendor/documents
     *
     * Upload a document. Vendor admin can only upload to their own vendor.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', VendorDocument::class);

        $request->validate([
            'document_type' => 'required|string|in:gst_certificate,pan,business_registration,cancelled_cheque,fuel_license,pollution_compliance,other',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expires_at'    => 'nullable|date|after:today',
        ]);

        $user = $request->user();
        $vendorId = $user->vendor_id;

        // If super_admin is uploading on behalf of a vendor
        if ($user->hasRole('super_admin') && $request->vendor_id) {
            $vendorId = $request->vendor_id;
        }

        if (! $vendorId) {
            return response()->json(['success' => false, 'message' => 'Vendor not identified.'], 403);
        }

        $path = $request->file('file')->store("vendor-documents/{$vendorId}", 'local');

        $document = VendorDocument::create([
            'vendor_id'     => $vendorId,
            'document_type' => $request->document_type,
            'file_path'     => $path,
            'status'        => DocumentStatus::Pending,
            'expires_at'    => $request->expires_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded and pending verification.',
            'data'    => new VendorDocumentResource($document),
        ], 201);
    }

    /**
     * POST /api/v1/admin/documents/{document}/verify
     *
     * Super Admin verifies a document.
     */
    public function verify(Request $request, VendorDocument $document): JsonResponse
    {
        $this->authorize('verify', $document);

        $document->update([
            'status'      => DocumentStatus::Verified,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document verified successfully.',
            'data'    => new VendorDocumentResource($document->fresh()),
        ]);
    }

    /**
     * POST /api/v1/admin/documents/{document}/reject
     *
     * Super Admin rejects a document.
     */
    public function reject(Request $request, VendorDocument $document): JsonResponse
    {
        $this->authorize('reject', $document);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $document->update([
            'status'           => DocumentStatus::Rejected,
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document rejected.',
            'data'    => new VendorDocumentResource($document->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/vendor/documents/{document}
     *
     * Vendor admin can delete only their own pending documents.
     */
    public function destroy(VendorDocument $document): JsonResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted.',
        ]);
    }
}
