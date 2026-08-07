<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    // Enquiry / Quote fields
    public ?string $type;
    public ?string $productName;
    public ?float  $quantity;
    public ?string $deliveryDate;
    public ?float  $price;
    public ?string $unit;
    public ?float  $minQty;
    public ?string $validity;
    public ?string $dispatch;
    public ?string $terms;

    // Extra fields for 'request' type — customer contact info + context
    public ?string $customerName;
    public ?string $customerCompany;
    public ?string $customerEmail;
    public ?string $customerPhone;
    public ?string $customerMessage;
    public ?string $listingSlug;
    public ?string $vendorName;

    public function __construct(
        ?string $type            = 'request',
        ?string $productName     = 'Bulk Fuel',
        ?float  $quantity        = 0.0,
        ?string $deliveryDate    = null,
        ?float  $price           = null,
        ?string $unit            = null,
        ?float  $minQty          = null,
        ?string $validity        = null,
        ?string $dispatch        = null,
        ?string $terms           = null,
        ?string $customerName    = null,
        ?string $customerCompany = null,
        ?string $customerEmail   = null,
        ?string $customerPhone   = null,
        ?string $customerMessage  = null,
        ?string $listingSlug     = null,
        ?string $vendorName      = null,
    ) {
        $this->type            = $type ?? 'request';
        $this->productName     = $productName ?? 'Bulk Fuel';
        $this->quantity        = (float) ($quantity ?? 0.0);
        $this->deliveryDate    = $deliveryDate;
        $this->price           = $price !== null ? (float) $price : null;
        $this->unit            = $unit;
        $this->minQty          = $minQty !== null ? (float) $minQty : null;
        $this->validity        = $validity;
        $this->dispatch        = $dispatch;
        $this->terms           = $terms;
        $this->customerName    = $customerName;
        $this->customerCompany = $customerCompany;
        $this->customerEmail   = $customerEmail;
        $this->customerPhone   = $customerPhone;
        $this->customerMessage  = $customerMessage;
        $this->listingSlug     = $listingSlug;
        $this->vendorName      = $vendorName;
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'request'
            ? 'New B2B Bulk Fuel Quote Request from ' . ($this->customerCompany ?: $this->customerName ?: 'a Customer')
            : 'Quotation Received - B2B Bulk Fuel';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote',
        );
    }
}
