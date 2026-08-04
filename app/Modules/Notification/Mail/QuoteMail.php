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

    public function __construct(
        public string $type, // 'request' or 'response'
        public string $productName,
        public float $quantity,
        public ?string $deliveryDate = null,
        public ?float $price = null,
        public ?string $unit = null,
        public ?float $minQty = null,
        public ?string $validity = null,
        public ?string $dispatch = null,
        public ?string $terms = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->type === 'request'
            ? 'New Bulk Fuel Quote Request'
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
