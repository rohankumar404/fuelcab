<?php

declare(strict_types=1);

namespace App\Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Http\Requests\QuoteInquiryRequest;
use App\Modules\Notification\Jobs\SendEmailJob;
use App\Modules\Notification\Mail\QuoteMail;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class QuoteController extends Controller
{
    use ApiResponse;

    /**
     * Receive a B2B bulk fuel RFQ / lead inquiry from the marketplace and
     * dispatch a QuoteMail (type='request') to admin for action.
     *
     * Route: POST /api/v1/marketplace/rfq   (public — no auth required)
     */
    public function store(QuoteInquiryRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $adminEmail = config('fuelcab.notifications.email.admin_email', 'admin@fuelcab.com');

        $mailable = new QuoteMail(
            type:            'request',
            productName:     $data['product_name']  ?? 'Bulk Fuel',
            quantity:        (float) ($data['quantity'] ?? 0),
            deliveryDate:    $data['delivery_date']  ?? null,
            customerName:    $data['name']           ?? null,
            customerCompany: $data['company']        ?? null,
            customerEmail:   $data['email']          ?? null,
            customerPhone:   $data['phone']          ?? null,
            customerMessage: $data['message']        ?? null,
            listingSlug:     $data['listing_slug']   ?? null,
            vendorName:      $data['vendor_name']    ?? null,
        );

        SendEmailJob::dispatch($adminEmail, $mailable);

        Log::info('[QuoteController] RFQ inquiry queued.', [
            'admin_email'  => $adminEmail,
            'from_name'    => $data['name']    ?? null,
            'from_email'   => $data['email']   ?? null,
            'product_name' => $data['product_name'] ?? null,
            'quantity'     => $data['quantity'] ?? null,
        ]);

        return $this->success(
            data: ['queued' => true],
            message: 'Your quote request has been submitted. Our team will be in touch shortly.'
        );
    }
}
