<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Handle Stripe Webhook calls.
     */
    public function stripe(Request $request): JsonResponse
    {
        // TODO: Verify signature and process event type (payment_intent.succeeded, charge.failed)
        return response()->json(['received' => true]);
    }

    /**
     * Handle Razorpay Webhook calls.
     */
    public function razorpay(Request $request): JsonResponse
    {
        // TODO: Verify signature and process payment updates
        return response()->json(['received' => true]);
    }
}
