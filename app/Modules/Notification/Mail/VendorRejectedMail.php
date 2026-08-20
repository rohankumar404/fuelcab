<?php

declare(strict_types=1);

namespace App\Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorRejectedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $contactPerson;

    public ?string $companyName;

    public ?string $reason;

    public function __construct(
        ?string $contactPerson = 'Vendor',
        ?string $companyName = 'Company',
        ?string $reason = 'Application details could not be verified'
    ) {
        $this->contactPerson = $contactPerson ?? 'Vendor';
        $this->companyName = $companyName ?? 'Company';
        $this->reason = $reason ?? 'Application details could not be verified';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vendor Application Update - FuelCab',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor_rejected',
        );
    }
}
