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

        // Documents (scoped to own vendor only)
        Route::get('documents',               [VendorDocumentController::class, 'index']);
        Route::post('documents',              [VendorDocumentController::class, 'store']);
        Route::delete('documents/{document}', [VendorDocumentController::class, 'destroy']);
    });

    // ── Super Admin & Operations: Full Vendor Management ───────────────────
    Route::prefix('admin/vendors')->group(function () {
        Route::get('/',                          [VendorController::class, 'index']);
        Route::get('/{vendor}',                  [VendorController::class, 'show']);
        Route::post('/{vendor}/approve',         [VendorController::class, 'approve']);
        Route::post('/{vendor}/reject',          [VendorController::class, 'reject']);
        Route::post('/{vendor}/suspend',         [VendorController::class, 'suspend']);
        Route::post('/{vendor}/reactivate',      [VendorController::class, 'reactivate']);
        Route::post('/{vendor}/notes',           [VendorController::class, 'addNotes']);
        Route::get('/{vendor}/documents',        [VendorDocumentController::class, 'index']);
    });

    // ── Admin: Document Verification ───────────────────────────────────────
    Route::prefix('admin/documents')->group(function () {
        Route::post('/{document}/verify', [VendorDocumentController::class, 'verify']);
        Route::post('/{document}/reject', [VendorDocumentController::class, 'reject']);
    });
});
