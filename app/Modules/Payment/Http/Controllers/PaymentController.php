<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Actions\InitiatePaymentAction;
use App\Modules\Payment\Actions\VerifyPaymentAction;
use App\Modules\Payment\Http\Requests\InitiatePaymentRequest;
use App\Modules\Payment\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * Initiate a payment order with a gateway.
     *
     * Route: POST /api/v1/payments/initiate
     */
    public function initiate(InitiatePaymentRequest $request, InitiatePaymentAction $action): JsonResponse
    {
        $dto = $action->execute(
            $request->input('order_id'),
            $request->input('payment_method'),
            (float) $request->input('amount'),
            $request->input('currency', 'INR')
        );

        return $this->success(
            data: $dto->toArray(),
            message: 'Payment initiated successfully.'
        );
    }

    /**
     * Verify a payment signature.
     *
     * Route: POST /api/v1/payments/verify
     */
    public function verify(Request $request, VerifyPaymentAction $action): JsonResponse
    {
        $payload = $request->validate([
            'razorpay_order_id'   => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature'  => ['required', 'string'],
        ]);

        $verified = $action->execute($payload, 'razorpay');

        if ($verified) {
            return $this->success(
                data: ['verified' => true],
                message: 'Payment verified and captured successfully.'
            );
        }

        return $this->error(
            message: 'Payment verification failed.',
            statusCode: 400
        );
    }

    /**
     * Get payment history for the authenticated user.
     *
     * Route: GET /api/v1/payments/history
     */
    public function history(Request $request): JsonResponse
    {
        $payments = Payment::with('order:id,status,total_amount,created_at')
            ->whereHas('order', fn ($q) => $q->where('customer_id', $request->user()->id))
            ->latest('created_at')
            ->paginate(15);

        return $this->success(
            data: $payments,
            message: 'Payment history retrieved successfully.'
        );
    }
}
