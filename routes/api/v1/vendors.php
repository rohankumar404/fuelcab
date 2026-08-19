<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Vendor\Http\Controllers\VendorController;
use App\Modules\Vendor\Http\Controllers\VendorDocumentController;

/*
|--------------------------------------------------------------------------
| Vendor Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // ── Vendor Self-Service (vendor_admin / vendor_staff) ──────────────────
    // These routes always scope to the authenticated user's own vendor.
    // There is NO vendor_id in the URL to prevent IDOR.
    Route::prefix('vendor')->group(function () {
        Route::get('profile', [VendorController::class, 'profile']);
        Route::put('profile', [VendorController::class, 'updateProfile']);
        Route::post('applications', [VendorController::class, 'submitApplication']);

        // Documents (scoped to own vendor only)
        Route::get('documents',               [VendorDocumentController::class, 'index']);
        Route::post('documents',              [VendorDocumentController::class, 'store']);
        Route::delete('documents/{document}', [VendorDocumentController::class, 'destroy']);
    });

    // ── Admin: Read-only vendor listing (super_admin, operations_team, vendor_admin, vendor_staff) ──
    // Vendor admins may list/view vendors via the API; VendorPolicy::viewAny() enforces
    // fine-grained scoping (vendor users only see their own vendor via the controller).
    Route::prefix('admin/vendors')
        ->middleware('role:super_admin,operations_team,vendor_admin,vendor_staff')
        ->group(function () {
            Route::get('/',                   [VendorController::class, 'index']);
            Route::get('/{vendor}',           [VendorController::class, 'show']);
            Route::get('/{vendor}/documents', [VendorDocumentController::class, 'index']);
        });

    // ── Admin: Write actions — strictly super_admin and operations_team only ────────────────
    Route::prefix('admin/vendors')
        ->middleware('role:super_admin,operations_team')
        ->group(function () {
            Route::post('/{vendor}/approve',    [VendorController::class, 'approve']);
            Route::post('/{vendor}/reject',     [VendorController::class, 'reject']);
            Route::post('/{vendor}/suspend',    [VendorController::class, 'suspend']);
            Route::post('/{vendor}/reactivate', [VendorController::class, 'reactivate']);
            Route::post('/{vendor}/notes',      [VendorController::class, 'addNotes']);
        });

    // ── Admin: Document Verification — super_admin and operations_team only ────────────────
    Route::prefix('admin/documents')
        ->middleware('role:super_admin,operations_team')
        ->group(function () {
            Route::post('/{document}/verify', [VendorDocumentController::class, 'verify']);
            Route::post('/{document}/reject', [VendorDocumentController::class, 'reject']);
        });
});
