<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $contactPerson;

    public ?string $companyName;

    public ?string $vendorCode;

    public function __construct(
        ?string $contactPerson = 'Vendor',
        ?string $companyName = 'Company',
        ?string $vendorCode = 'N/A'
    ) {
        $this->contactPerson = $contactPerson ?? 'Vendor';
        $this->companyName = $companyName ?? 'Company';
        $this->vendorCode = $vendorCode ?? 'N/A';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Vendor Application Has Been Approved!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor_approved',
        );
    }
}
