<?php

declare(strict_types=1);

use App\Modules\Notification\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notifications Routes — API v1
|--------------------------------------------------------------------------
*/

// ── Public (no auth required) ─────────────────────────────────────────────
// B2B RFQ / Lead Inquiry from marketplace listing page
Route::post('marketplace/rfq', [QuoteController::class, 'store'])->name('marketplace.rfq.store');
